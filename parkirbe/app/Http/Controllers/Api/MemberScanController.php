<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MemberScanResource;
use App\Services\MemberScanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * MemberScanController memproses scan kartu QR member di pintu masuk.
 *
 * POST /members/scan  → kirim {member_code}
 */
class MemberScanController extends Controller
{
    public function __construct(
        private readonly MemberScanService $scanService,
    ) {}

    /**
     * Proses scan kartu member.
     * Mengembalikan status izin masuk, alasan, dan data member.
     *
     * @param Request $request
     * @return JsonResponse HTTP 200 (allowed) | HTTP 422 (denied)
     */
    public function scan(Request $request): JsonResponse
    {
        $request->validate([
            'member_code' => 'required|string|max:50',
        ]);

        $result = $this->scanService->scan(
            $request->string('member_code')->toString()
        );

        $statusCode = $result['allowed'] ? 200 : 422;

        return response()->json(
            new MemberScanResource($result),
            $statusCode
        );
    }
}
