<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BillStatus;
use App\Exceptions\Domain\BillAlreadyPaidException;
use App\Exceptions\Domain\InsufficientCashException;
use App\Mail\MemberBillReminderMail;
use App\Models\Member;
use App\Models\MemberBill;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * MemberBillService mengelola tagihan bulanan member.
 *
 * Command parking:generate-bills memanggil generateForMonth() setiap awal bulan.
 * Petugas dapat memanggil endpoint manual jika diperlukan.
 */
class MemberBillService
{
    public function __construct(
        private readonly InvoiceNumberGenerator $invoiceGenerator,
        private readonly FonnteService          $fonnteService,
    ) {}

    /**
     * Generate tagihan untuk semua member aktif pada periode tertentu.
     * IDEMPOTENT: Tidak akan membuat duplikat jika tagihan sudah ada
     * (memanfaatkan unique constraint member_id + period).
     *
     * @param Carbon $period Bulan yang dituju (harus tanggal 1)
     * @return int Jumlah tagihan baru yang dibuat
     */
    public function generateForMonth(Carbon $period): int
    {
        $period    = $period->startOfMonth()->copy(); // Pastikan selalu tgl 1
        $members   = Member::where('is_active', true)->get();
        $generated = 0;

        foreach ($members as $member) {
            $created = $this->generateForPeriod($member, $period);
            if ($created) {
                $generated++;
            }
        }

        return $generated;
    }

    /**
     * Generate satu tagihan untuk member + periode tertentu.
     * Return false jika tagihan sudah ada (idempotent).
     *
     * @param Member $member
     * @param Carbon $period
     * @return bool true jika tagihan baru dibuat
     */
    public function generateForPeriod(Member $member, Carbon $period): bool
    {
        $periodDate = $period->startOfMonth()->toDateString();

        // Cek apakah sudah ada (mencegah duplikat)
        $exists = MemberBill::where('member_id', $member->id)
            ->where('period', $periodDate)
            ->exists();

        if ($exists) {
            return false;
        }

        MemberBill::create([
            'member_id' => $member->id,
            'period'    => $periodDate,
            'amount'    => $member->monthly_fee,
            'status'    => BillStatus::BelumLunas,
        ]);

        return true;
    }

    /**
     * Melunaskan tagihan member.
     * Menggunakan DB transaction + lockForUpdate() untuk mencegah pembayaran ganda.
     *
     * @param MemberBill $bill
     * @param int        $cashReceived  Uang tunai dalam Rupiah
     * @param User       $processedBy   Petugas yang memproses
     * @return MemberBill Tagihan yang sudah diupdate
     *
     * @throws BillAlreadyPaidException   Jika tagihan sudah dilunaskan
     * @throws InsufficientCashException  Jika uang tunai tidak cukup
     */
    public function pay(MemberBill $bill, int $cashReceived, User $processedBy): MemberBill
    {
        return DB::transaction(function () use ($bill, $cashReceived, $processedBy) {
            // Lock row untuk mencegah race condition
            $bill = MemberBill::where('id', $bill->id)->lockForUpdate()->firstOrFail();

            if ($bill->isPaid()) {
                throw new BillAlreadyPaidException($bill->id);
            }

            if ($cashReceived < $bill->amount) {
                throw new InsufficientCashException($bill->amount, $cashReceived);
            }

            $period        = Carbon::parse($bill->period);
            $invoiceNumber = $this->invoiceGenerator->forMember($period);

            $bill->update([
                'status'         => BillStatus::Lunas,
                'cash_received'  => $cashReceived,
                'change_amount'  => $cashReceived - $bill->amount,
                'paid_at'        => now(),
                'processed_by'   => $processedBy->id,
                'invoice_number' => $invoiceNumber,
            ]);

            return $bill->fresh();
        });
    }

    /**
     * Mengecek apakah member memiliki tagihan yang belum lunas.
     *
     * @param Member $member
     * @return bool true jika ada tagihan belum lunas
     */
    public function hasUnpaid(Member $member): bool
    {
        return $member->bills()->where('status', BillStatus::BelumLunas->value)->exists();
    }

    /**
     * Mengirim pesan pengingat tagihan member via WhatsApp (Fonnte) dan/atau Email (SMTP).
     * Dapat dipilih channel: 'auto' (otomatis yang tersedia), 'whatsapp', 'email', atau 'all'.
     *
     * @param MemberBill $bill
     * @param string     $channel 'auto' | 'whatsapp' | 'email' | 'all'
     * @return array{whatsapp: bool, email: bool, messages: string[]}
     */
    public function sendReminder(MemberBill $bill, string $channel = 'auto'): array
    {
        $bill->loadMissing('member');
        $member = $bill->member;

        $results = [
            'whatsapp' => false,
            'email'    => false,
            'messages' => [],
        ];

        if (empty($member->phone_number) && empty($member->email)) {
            $results['messages'][] = 'Member ini belum memiliki nomor telepon maupun alamat email.';
            return $results;
        }

        $periodFormatted  = $bill->period ? Carbon::parse($bill->period)->translatedFormat('F Y') : date('F Y');
        $nominalFormatted = 'Rp ' . number_format($bill->amount, 0, ',', '.');

        $shouldSendWa    = in_array($channel, ['auto', 'whatsapp', 'all'], true);
        $shouldSendEmail = in_array($channel, ['auto', 'email', 'all'], true);

        // 1. Kirim via WhatsApp (Fonnte Gateway) jika dipilih & nomor tersedia
        if ($shouldSendWa) {
            if (!empty($member->phone_number)) {
                $waMessage = "Halo Bpk/Ibu *{$member->name}*,\n\n"
                    . "Masa berlaku kartu langganan parkir resmi Anda di *Plaza Andalas Padang* ({$member->member_code} - {$member->plate_number}) telah habis / menunggu perpanjangan iuran bulanan periode *{$periodFormatted}*.\n\n"
                    . "Total iuran yang harus dibayar: *{$nominalFormatted}*.\n\n"
                    . "Mohon datangi petugas kasir parkir Plaza Andalas untuk melakukan pembayaran perpanjangan agar akses gerbang otomatis tetap aktif.\n\n"
                    . "_Pesan otomatis E-Parkir Plaza Andalas_";

                $fonnteResult = $this->fonnteService->sendWhatsAppMessage($member->phone_number, $waMessage);
                $results['whatsapp'] = $fonnteResult['success'];
                $results['messages'][] = $fonnteResult['message'];
            } elseif ($channel === 'whatsapp') {
                $results['messages'][] = 'Nomor WhatsApp belum terdaftar pada profil member.';
            }
        }

        // 2. Kirim via Email (SMTP) jika dipilih & email tersedia
        if ($shouldSendEmail) {
            if (!empty($member->email)) {
                try {
                    Mail::to($member->email)->send(new MemberBillReminderMail($member, $bill));
                    $results['email'] = true;
                    $results['messages'][] = "Email pengingat berhasil dikirim ke {$member->email}.";
                } catch (\Throwable $e) {
                    Log::error("[Reminder Email Gagal] {$member->email}: " . $e->getMessage());
                    $results['messages'][] = "Gagal kirim email: " . $e->getMessage();
                }
            } elseif ($channel === 'email') {
                $results['messages'][] = 'Alamat email belum terdaftar pada profil member.';
            }
        }

        return $results;
    }
}
