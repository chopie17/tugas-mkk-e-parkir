<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MemberBill;
use App\Models\ParkingTicket;
use Carbon\Carbon;

/**
 * InvoiceNumberGenerator membuat nomor invoice yang unik dan berurutan.
 *
 * Format:
 * - Non Member: INV-NM-YYYYMMDD-0001 (nomor urut per hari)
 * - Member:     INV-M-YYYYMM-0001    (nomor urut per bulan)
 */
class InvoiceNumberGenerator
{
    /**
     * Generate nomor invoice untuk tiket parkir non-member.
     * Nomor urut di-reset setiap hari.
     *
     * @param Carbon $date Tanggal transaksi
     * @return string
     */
    public function forNonMember(Carbon $date): string
    {
        $prefix = 'INV-NM-' . $date->format('Ymd') . '-';

        // Hitung invoice hari ini untuk menentukan nomor urut
        $count = ParkingTicket::where('invoice_number', 'like', $prefix . '%')->count();
        $seq   = str_pad((string)($count + 1), 4, '0', STR_PAD_LEFT);

        return $prefix . $seq;
    }

    /**
     * Generate nomor invoice untuk tagihan member bulanan.
     * Nomor urut di-reset setiap bulan.
     *
     * @param Carbon $period Periode tagihan (bulan)
     * @return string
     */
    public function forMember(Carbon $period): string
    {
        $prefix = 'INV-M-' . $period->format('Ym') . '-';

        // Hitung invoice bulan ini untuk menentukan nomor urut
        $count = MemberBill::where('invoice_number', 'like', $prefix . '%')->count();
        $seq   = str_pad((string)($count + 1), 4, '0', STR_PAD_LEFT);

        return $prefix . $seq;
    }
}
