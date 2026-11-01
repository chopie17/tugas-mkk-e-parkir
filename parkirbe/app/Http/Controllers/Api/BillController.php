<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bill\PayBillRequest;
use App\Http\Resources\MemberBillResource;
use App\Models\MemberBill;
use App\Services\MemberBillService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * BillController mengelola tagihan member.
 *
 * GET  /bills                → index (daftar semua tagihan, filter opsional)
 * GET  /bills/{bill}         → show (detail tagihan)
 * POST /bills/{bill}/pay     → pay (lunaskan tagihan)
 * POST /bills/generate       → generate tagihan bulan ini (super admin)
 */
class BillController extends Controller
{
    public function __construct(
        private readonly MemberBillService $billService,
    ) {}

    /**
     * Daftar tagihan. Petugas hanya lihat tagihan yang diproses olehnya.
     * Super Admin lihat semua. Filter: status, member_id, date.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = MemberBill::with(['member', 'processor'])->latest('period');

        // Petugas biasa hanya boleh lihat data yang diproses olehnya
        if ($request->user()->role->value === 'petugas') {
            $query->where('processed_by', $request->user()->id)
                  ->orWhereNull('processed_by'); // Termasuk yang belum diproses
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('member_id')) {
            $query->where('member_id', $request->input('member_id'));
        }

        $bills = $query->paginate(15);

        return response()->json([
            'data' => MemberBillResource::collection($bills->items()),
            'meta' => [
                'current_page' => $bills->currentPage(),
                'last_page'    => $bills->lastPage(),
                'total'        => $bills->total(),
            ],
        ]);
    }

    /**
     * Detail tagihan.
     *
     * @param MemberBill $bill
     * @return JsonResponse
     */
    public function show(MemberBill $bill): JsonResponse
    {
        return response()->json([
            'bill' => new MemberBillResource($bill->load(['member', 'processor'])),
        ]);
    }

    /**
     * Melunaskan tagihan member.
     *
     * @param PayBillRequest $request
     * @param MemberBill     $bill
     * @return JsonResponse
     */
    public function pay(PayBillRequest $request, MemberBill $bill): JsonResponse
    {
        $paid = $this->billService->pay(
            bill:         $bill,
            cashReceived: (int) $request->input('cash_received'),
            processedBy:  $request->user(),
        );

        return response()->json([
            'message' => 'Tagihan berhasil dilunaskan.',
            'bill'    => new MemberBillResource($paid->load(['member', 'processor'])),
        ]);
    }

    /**
     * Generate tagihan untuk semua member aktif bulan ini.
     * HANYA Super Admin yang boleh menjalankan ini.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generate(Request $request): JsonResponse
    {
        $count = $this->billService->generateForMonth(now());

        return response()->json([
            'message'   => "Berhasil generate {$count} tagihan baru untuk bulan ini.",
            'generated' => $count,
        ]);
    }
}
