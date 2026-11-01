<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\MemberBillResource;
use App\Http\Resources\ParkingTicketResource;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ReportController menyediakan laporan transaksi harian/bulanan.
 *
 * GET /reports/non-member   → Laporan tiket non-member (Paid)
 * GET /reports/member       → Laporan tagihan member (Lunas)
 * GET /reports/summary      → Ringkasan statistik (Super Admin)
 */
class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService,
    ) {}

    /**
     * Laporan tiket non-member yang sudah dibayar.
     * Petugas hanya melihat transaksi miliknya.
     *
     * Query params: search, date_from, date_to, per_page
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function nonMember(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'date_from', 'date_to']);

        // Petugas hanya lihat transaksi miliknya
        if ($request->user()->role === UserRole::Petugas) {
            $filters['petugas_id'] = $request->user()->id;
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $tickets = $this->reportService->nonMember($filters)->paginate($perPage);

        return response()->json([
            'data' => ParkingTicketResource::collection($tickets->items()),
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'last_page'    => $tickets->lastPage(),
                'total'        => $tickets->total(),
                'total_fee'    => (clone $this->reportService->nonMember($filters))->sum('fee'),
            ],
        ]);
    }

    /**
     * Laporan tagihan member yang sudah lunas.
     * Petugas hanya melihat tagihan yang diproses olehnya.
     *
     * Query params: search, date_from, date_to, per_page
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function member(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'date_from', 'date_to']);

        // Petugas hanya lihat transaksi miliknya
        if ($request->user()->role === UserRole::Petugas) {
            $filters['petugas_id'] = $request->user()->id;
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $bills   = $this->reportService->member($filters)->paginate($perPage);

        return response()->json([
            'data' => MemberBillResource::collection($bills->items()),
            'meta' => [
                'current_page' => $bills->currentPage(),
                'last_page'    => $bills->lastPage(),
                'total'        => $bills->total(),
                'total_amount' => (clone $this->reportService->member($filters))->sum('amount'),
            ],
        ]);
    }

    /**
     * Ringkasan statistik hari ini.
     * Hanya Super Admin yang dapat mengakses.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function summary(Request $request): JsonResponse
    {
        $today = now()->toDateString();

        $nmTickets  = $this->reportService->nonMember(['date_from' => $today, 'date_to' => $today]);
        $memberBills = $this->reportService->member(['date_from' => $today, 'date_to' => $today]);

        return response()->json([
            'today' => $today,
            'non_member' => [
                'transactions' => $nmTickets->count(),
                'total_fee'    => $nmTickets->sum('fee'),
            ],
            'member' => [
                'transactions' => $memberBills->count(),
                'total_amount' => $memberBills->sum('amount'),
            ],
            'grand_total' => $nmTickets->sum('fee') + $memberBills->sum('amount'),
        ]);
    }
}
