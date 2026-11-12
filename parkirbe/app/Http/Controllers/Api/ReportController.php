<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\MemberBillResource;
use App\Http\Resources\ParkingTicketResource;
use App\Models\MemberEntry;
use App\Models\ParkingTicket;
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

        $nmTickets   = $this->reportService->nonMember(['date_from' => $today, 'date_to' => $today]);
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

    /**
     * Log aktivitas kendaraan hari ini (Non-Member & Member, baik yang baru masuk maupun sudah keluar).
     */
    public function todayActivity(Request $request): JsonResponse
    {
        $today = now()->toDateString();

        // 1. Ambil semua tiket non-member hari ini (masuk hari ini atau keluar hari ini)
        $tickets = ParkingTicket::with('processor')
            ->where(function ($q) use ($today) {
                $q->whereDate('entry_at', $today)
                  ->orWhereDate('created_at', $today)
                  ->orWhereDate('exit_at', $today);
            })
            ->latest('entry_at')
            ->limit(50)
            ->get()
            ->map(function ($ticket) {
                return [
                    'id'               => 'ticket-' . $ticket->id,
                    'type'             => 'non_member',
                    'token'            => $ticket->token,
                    'plate_number'     => $ticket->plate_number, // null saat baru masuk
                    'vehicle_category' => $ticket->vehicle_category?->value,
                    'entry_at'         => $ticket->entry_at?->toIso8601String(),
                    'exit_at'          => $ticket->exit_at?->toIso8601String(),
                    'fee'              => $ticket->fee,
                    'status'           => $ticket->status->value, // 'active' (masih parkir) atau 'paid'
                    'invoice_number'   => $ticket->invoice_number,
                    'created_at'       => $ticket->created_at?->toIso8601String(),
                ];
            });

        // 2. Ambil aktivitas scan member hari ini (result: allowed)
        $memberEntries = MemberEntry::with('member')
            ->whereDate('scanned_at', $today)
            ->where('result', \App\Enums\EntryResult::Allowed)
            ->latest('scanned_at')
            ->limit(50)
            ->get()
            ->map(function ($entry) {
                $member = $entry->member;
                return [
                    'id'               => 'member-' . $entry->id,
                    'type'             => 'member',
                    'token'            => $member?->member_code ?? ('MBR-' . $entry->member_id),
                    'name'             => $member?->name,
                    'plate_number'     => $member?->plate_number,
                    'vehicle_category' => $member?->vehicle_category?->value,
                    'entry_at'         => $entry->direction === 'in' ? $entry->scanned_at->toIso8601String() : $member?->last_entry_at?->toIso8601String(),
                    'exit_at'          => $entry->direction === 'out' ? $entry->scanned_at->toIso8601String() : null,
                    'fee'              => 0, // Member Rp 0
                    'status'           => $entry->direction === 'in' ? 'active' : 'paid',
                    'invoice_number'   => null,
                    'created_at'       => $entry->scanned_at->toIso8601String(),
                ];
            });

        // Gabungkan dan urutkan berdasarkan waktu terbaru
        $all = $tickets->concat($memberEntries)
            ->sortByDesc(fn ($item) => $item['exit_at'] ?? $item['entry_at'] ?? $item['created_at'])
            ->values();

        return response()->json([
            'data'  => $all,
            'total' => $all->count(),
        ]);
    }
}
