<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TariffType;
use App\Enums\VehicleCategory;
use App\Models\Tariff;
use Carbon\Carbon;

/**
 * ParkingFeeCalculator menghitung biaya parkir non-member.
 *
 * Aturan:
 * - Durasi dihitung dalam menit, lalu dibulatkan ke atas ke jam penuh (ceil)
 * - Minimum 1 jam
 * - Total = jumlah_jam × tarif_per_jam[kategori]
 * - Tarif diambil dari database (tabel tariffs), bukan hard-coded
 */
class ParkingFeeCalculator
{
    /**
     * Menghitung biaya parkir berdasarkan waktu masuk, keluar, dan kategori kendaraan.
     *
     * @param Carbon         $entryAt   Waktu masuk parkir
     * @param Carbon         $exitAt    Waktu keluar parkir
     * @param VehicleCategory $category  Kategori kendaraan (motor/mobil)
     * @return array{hours: int, fee: int, tariff_per_hour: int}
     */
    public function calculate(Carbon $entryAt, Carbon $exitAt, VehicleCategory $category): array
    {
        // Hitung durasi dalam menit, lalu ceil ke jam
        $durationMinutes = (int) ceil($entryAt->diffInMinutes($exitAt));
        $hours           = max(1, (int) ceil($durationMinutes / 60));

        // Ambil tarif dari database
        $tariffPerHour = Tariff::getAmount(TariffType::NonMemberHourly, $category) ?? 0;

        $fee = $hours * $tariffPerHour;

        return [
            'hours'           => $hours,
            'fee'             => $fee,
            'tariff_per_hour' => $tariffPerHour,
        ];
    }
}
