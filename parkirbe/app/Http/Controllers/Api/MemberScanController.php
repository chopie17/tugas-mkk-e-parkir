<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MemberScanResource;
use App\Services\MemberScanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * MemberScanController memproses scan kartu QR / plat member di pintu masuk dan pintu keluar.
 *
 * POST /members/scan  → kirim {member_code, direction?: 'in'|'out'}
 */
class MemberScanController extends Controller
{
    public function __construct(
        private readonly MemberScanService $scanService,
    ) {}

    /**
     * Proses scan kartu member.
     * Mengembalikan status izin, alasan, arah, dan data member.
     */
    public function scan(Request $request): JsonResponse
    {
        $request->validate([
            'member_code' => 'required|string|max:50',
            'direction'   => 'nullable|string|in:in,out',
        ]);

        $direction = $request->input('direction', 'in');

        $result = $this->scanService->scan(
            $request->string('member_code')->toString(),
            $direction
        );

        $statusCode = $result['allowed'] ? 200 : 422;

        return response()->json(
            new MemberScanResource($result),
            $statusCode
        );
    }
}
