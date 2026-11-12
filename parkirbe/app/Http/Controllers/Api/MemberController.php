<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\StoreMemberRequest;
use App\Http\Requests\Member\UpdateMemberRequest;
use App\Http\Resources\MemberBillResource;
use App\Http\Resources\MemberResource;
use App\Models\Member;
use App\Services\MemberBillService;
use App\Services\MemberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * MemberController mengelola data member parkir.
 *
 * GET    /members           → index (daftar)
 * POST   /members           → store (daftar baru)
 * GET    /members/{member}  → show (detail + tagihan)
 * PUT    /members/{member}  → update
 * DELETE /members/{member}  → destroy (soft delete)
 * GET    /members/{member}/bills → daftar tagihan member
 * POST   /members/scan      → scan kartu QR
 */
class MemberController extends Controller
{
    public function __construct(
        private readonly MemberService     $memberService,
        private readonly MemberBillService $billService,
    ) {}

    /**
     * Daftar semua member dengan filter opsional.
     * Query param: search (nama/plat), is_active (true/false)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Member::query()->with([
            'creator',
            'bills' => fn($q) => $q->latest('period')->limit(5),
        ]);

        if ($request->filled('search')) {
            $s = $request->string('search');
            $query->where(fn($q) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('company_name', 'like', "%{$s}%")
                ->orWhere('plate_number', 'like', "%{$s}%")
                ->orWhere('member_code', 'like', "%{$s}%")
                ->orWhere('phone_number', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%"));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $members = $query->latest()->paginate(15);

        return response()->json([
            'data' => MemberResource::collection($members->items()),
            'meta' => [
                'current_page' => $members->currentPage(),
                'last_page'    => $members->lastPage(),
                'total'        => $members->total(),
            ],
        ]);
    }

    /**
     * Mendaftarkan member baru.
     *
     * @param StoreMemberRequest $request
     * @return JsonResponse
     */
    public function store(StoreMemberRequest $request): JsonResponse
    {
        $member = $this->memberService->create(
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'message' => 'Member berhasil didaftarkan.',
            'member'  => new MemberResource($member),
        ], 201);
    }

    /**
     * Detail member beserta tagihan bulan berjalan.
     *
     * @param Member $member
     * @return JsonResponse
     */
    public function show(Member $member): JsonResponse
    {
        $member->load(['bills' => fn($q) => $q->latest('period')->take(6)]);

        return response()->json([
            'member' => new MemberResource($member),
        ]);
    }

    /**
     * Update data member.
     *
     * @param UpdateMemberRequest $request
     * @param Member              $member
     * @return JsonResponse
     */
    public function update(UpdateMemberRequest $request, Member $member): JsonResponse
    {
        $updated = $this->memberService->update($member, $request->validated());

        return response()->json([
            'message' => 'Data member berhasil diperbarui.',
            'member'  => new MemberResource($updated),
        ]);
    }

    /**
     * Soft-delete member.
     *
     * @param Member $member
     * @return JsonResponse
     */
    public function destroy(Member $member): JsonResponse
    {
        $this->memberService->delete($member);

        return response()->json(['message' => 'Member berhasil dihapus.']);
    }

    /**
     * Daftar tagihan member dengan filter status.
     *
     * @param Request $request
     * @param Member  $member
     * @return JsonResponse
     */
    public function bills(Request $request, Member $member): JsonResponse
    {
        $query = $member->bills()->with('processor');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $bills = $query->latest('period')->paginate(12);

        return response()->json([
            'data' => MemberBillResource::collection($bills->items()),
            'meta' => [
                'current_page' => $bills->currentPage(),
                'last_page'    => $bills->lastPage(),
                'total'        => $bills->total(),
            ],
        ]);
    }
}
