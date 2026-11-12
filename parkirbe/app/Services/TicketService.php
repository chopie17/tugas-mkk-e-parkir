<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TicketStatus;
use App\Enums\VehicleCategory;
use App\Exceptions\Domain\InsufficientCashException;
use App\Exceptions\Domain\TicketAlreadyPaidException;
use App\Exceptions\Domain\TicketNotFoundException;
use App\Models\ParkingTicket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * TicketService mengelola siklus tiket parkir non-member.
 *
 * Alur: issue() → findByToken() → pay()
 */
class TicketService
{
    public function __construct(
        private readonly ParkingFeeCalculator   $calculator,
        private readonly InvoiceNumberGenerator $invoiceGenerator,
    ) {}

    /**
     * Membuat tiket parkir baru saat kendaraan masuk.
     * Token adalah 6 karakter unik (huruf besar + angka).
     *
     * @return ParkingTicket Tiket yang baru dibuat
     */
    public function issue(): ParkingTicket
    {
        $token = $this->generateUniqueToken();

        return ParkingTicket::create([
            'token'    => $token,
            'entry_at' => now(),
            'status'   => TicketStatus::Active,
        ]);
    }

    /**
     * Mencari tiket berdasarkan token.
     *
     * @param string $token
     * @return ParkingTicket
     * @throws TicketNotFoundException Jika tiket tidak ditemukan
     */
    public function findByToken(string $token): ParkingTicket
    {
        $normalized = strtoupper(trim($token));
        $ticket = ParkingTicket::where('token', $normalized)->first();

        if (!$ticket) {
            throw new TicketNotFoundException($normalized);
        }

        return $ticket;
    }

    /**
     * Memproses pembayaran tiket parkir non-member.
     * Menggunakan DB transaction + lockForUpdate() untuk mencegah race condition
     * (pembayaran ganda dari dua petugas sekaligus).
     *
     * @param ParkingTicket  $ticket
     * @param VehicleCategory $category
     * @param string          $plateNumber
     * @param int             $cashReceived  Uang tunai dalam Rupiah
     * @param User            $processedBy   Petugas yang memproses
     * @return ParkingTicket  Tiket yang sudah diupdate
     *
     * @throws TicketAlreadyPaidException  Jika tiket sudah pernah dibayar
     * @throws InsufficientCashException   Jika uang tunai tidak cukup
     */
    public function pay(
        ParkingTicket $ticket,
        VehicleCategory $category,
        string $plateNumber,
        int $cashReceived,
        User $processedBy,
    ): ParkingTicket {
        return DB::transaction(function () use ($ticket, $category, $plateNumber, $cashReceived, $processedBy) {
            // Lock row agar tidak ada race condition pembayaran ganda
            $ticket = ParkingTicket::where('id', $ticket->id)->lockForUpdate()->firstOrFail();

            if ($ticket->isPaid()) {
                throw new TicketAlreadyPaidException($ticket->token);
            }

            $exitAt = now();
            $calc   = $this->calculator->calculate($ticket->entry_at, $exitAt, $category);

            if ($cashReceived < $calc['fee']) {
                throw new InsufficientCashException($calc['fee'], $cashReceived);
            }

            $invoiceNumber = $this->invoiceGenerator->forNonMember($exitAt);

            $ticket->update([
                'exit_at'          => $exitAt,
                'status'           => TicketStatus::Paid,
                'vehicle_category' => $category,
                'plate_number'     => strtoupper(trim($plateNumber)),
                'duration_hours'   => $calc['hours'],
                'fee'              => $calc['fee'],
                'cash_received'    => $cashReceived,
                'change_amount'    => $cashReceived - $calc['fee'],
                'processed_by'     => $processedBy->id,
                'invoice_number'   => $invoiceNumber,
            ]);

            return $ticket->fresh();
        });
    }

    /**
     * Preview kalkulasi tarif tanpa menyimpan (untuk tampilan sebelum bayar).
     *
     * @param ParkingTicket   $ticket
     * @param VehicleCategory $category
     * @return array{hours: int, fee: int, tariff_per_hour: int}
     */
    public function previewFee(ParkingTicket $ticket, VehicleCategory $category): array
    {
        return $this->calculator->calculate($ticket->entry_at, now(), $category);
    }

    /**
     * Generate token unik 6 karakter (A-Z, 0-9).
     * Loop sampai mendapat token yang belum dipakai.
     */
    private function generateUniqueToken(): string
    {
        do {
            $token = strtoupper(Str::random(6));
        } while (ParkingTicket::where('token', $token)->exists());

        return $token;
    }
}
