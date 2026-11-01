<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BillStatus;
use App\Enums\TicketStatus;
use App\Models\MemberBill;
use App\Models\ParkingTicket;
use Illuminate\Database\Eloquent\Builder;

/**
 * ReportService menyediakan query builder untuk laporan transaksi.
 * Filter yang didukung: search (plat/token/nama), date_from, date_to, petugas_id.
 * Petugas hanya melihat laporan miliknya; Super Admin melihat semua.
 */
class ReportService
{
    /**
     * Query laporan transaksi non-member (tiket yang sudah dibayar).
     *
     * @param array $filters  Keys: search, date_from, date_to, petugas_id (optional)
     * @return Builder
     */
    public function nonMember(array $filters): Builder
    {
        $query = ParkingTicket::with('processor')
            ->where('status', TicketStatus::Paid);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('token', 'like', "%{$search}%")
                  ->orWhere('plate_number', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('exit_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('exit_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['petugas_id'])) {
            $query->where('processed_by', $filters['petugas_id']);
        }

        return $query->latest('exit_at');
    }

    /**
     * Query laporan pembayaran member (tagihan yang sudah lunas).
     *
     * @param array $filters  Keys: search, date_from, date_to, petugas_id (optional)
     * @return Builder
     */
    public function member(array $filters): Builder
    {
        $query = MemberBill::with(['member', 'processor'])
            ->where('status', BillStatus::Lunas);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('member', function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('plate_number', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('paid_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('paid_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['petugas_id'])) {
            $query->where('processed_by', $filters['petugas_id']);
        }

        return $query->latest('paid_at');
    }
}
