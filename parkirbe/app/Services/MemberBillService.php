<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BillStatus;
use App\Exceptions\Domain\BillAlreadyPaidException;
use App\Exceptions\Domain\InsufficientCashException;
use App\Models\Member;
use App\Models\MemberBill;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
}
