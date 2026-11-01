<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\TariffType;
use App\Enums\VehicleCategory;
use App\Http\Controllers\Controller;
use App\Http\Resources\TariffResource;
use App\Models\Tariff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

/**
 * TariffController mengelola tarif parkir.
 *
 * GET /tariffs           → index (semua tarif, publik)
 * PUT /tariffs/{tariff}  → update (Super Admin only)
 */
class TariffController extends Controller
{
    /**
     * Daftar semua tarif. Endpoint ini publik (tidak perlu login)
     * sehingga gate display harga bisa langsung pakai API.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $tariffs = Tariff::all();

        return response()->json([
            'tariffs' => TariffResource::collection($tariffs),
        ]);
    }

    /**
     * Update tarif (Super Admin only).
     *
     * @param Request $request
     * @param Tariff  $tariff
     * @return JsonResponse
     */
    public function update(Request $request, Tariff $tariff): JsonResponse
    {
        $request->validate([
            'amount' => 'required|integer|min:0',
        ]);

        $tariff->update(['amount' => (int) $request->input('amount')]);

        return response()->json([
            'message' => 'Tarif berhasil diperbarui.',
            'tariff'  => new TariffResource($tariff->fresh()),
        ]);
    }
}
