<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TariffType;
use App\Enums\VehicleCategory;
use App\Models\Tariff;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk data tarif parkir default.
 * Non-member: per jam. Member: per bulan.
 */
class TariffSeeder extends Seeder
{
    /**
     * Seed tariff awal.
     * Semua nilai dalam integer Rupiah.
     */
    public function run(): void
    {
        $tariffs = [
            // Tarif non-member per jam
            ['type' => TariffType::NonMemberHourly, 'vehicle_category' => VehicleCategory::Motor, 'amount' => 2000],
            ['type' => TariffType::NonMemberHourly, 'vehicle_category' => VehicleCategory::Mobil, 'amount' => 5000],
            // Tarif member per bulan
            ['type' => TariffType::MemberMonthly,   'vehicle_category' => VehicleCategory::Motor, 'amount' => 150000],
            ['type' => TariffType::MemberMonthly,   'vehicle_category' => VehicleCategory::Mobil, 'amount' => 200000],
        ];

        foreach ($tariffs as $tariff) {
            Tariff::updateOrCreate(
                [
                    'type'             => $tariff['type'],
                    'vehicle_category' => $tariff['vehicle_category'],
                ],
                ['amount' => $tariff['amount']]
            );
        }

        $this->command->info('Tariff seeded: 4 records');
    }
}
