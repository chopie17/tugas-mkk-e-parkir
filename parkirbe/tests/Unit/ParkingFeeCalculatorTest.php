<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\TariffType;
use App\Enums\VehicleCategory;
use App\Models\Tariff;
use App\Services\ParkingFeeCalculator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit test untuk ParkingFeeCalculator.
 *
 * Skenario:
 * - T01: Durasi tepat 1 jam (Motor) → 1 jam × tarif
 * - T02: Durasi 1 jam 1 menit (Mobil) → dibulatkan ke 2 jam
 * - T03: Durasi kurang dari 1 jam → minimum 1 jam
 */
class ParkingFeeCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private ParkingFeeCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed tarif agar calculator bisa ambil dari DB
        Tariff::create(['type' => TariffType::NonMemberHourly, 'vehicle_category' => VehicleCategory::Motor, 'amount' => 2000]);
        Tariff::create(['type' => TariffType::NonMemberHourly, 'vehicle_category' => VehicleCategory::Mobil, 'amount' => 5000]);

        $this->calculator = new ParkingFeeCalculator();
    }

    /**
     * T01: Durasi tepat 1 jam, kendaraan Motor.
     * Ekspektasi: 1 jam × Rp 2.000 = Rp 2.000
     */
    public function test_T01_fee_motor_1_hour(): void
    {
        $entry = Carbon::parse('2025-01-01 08:00:00');
        $exit  = Carbon::parse('2025-01-01 09:00:00');
        $calc  = $this->calculator->calculate($entry, $exit, VehicleCategory::Motor);

        $this->assertEquals(1, $calc['hours']);
        $this->assertEquals(2000, $calc['fee']);
        $this->assertEquals(2000, $calc['tariff_per_hour']);
    }

    /**
     * T02: Durasi 1 jam 1 menit, kendaraan Mobil.
     * Ekspektasi: dibulatkan ke 2 jam → 2 × Rp 5.000 = Rp 10.000
     */
    public function test_T02_fee_mobil_ceil_to_2_hours(): void
    {
        $entry = Carbon::parse('2025-01-01 08:00:00');
        $exit  = Carbon::parse('2025-01-01 09:01:00');
        $calc  = $this->calculator->calculate($entry, $exit, VehicleCategory::Mobil);

        $this->assertEquals(2, $calc['hours']);
        $this->assertEquals(10000, $calc['fee']);
    }

    /**
     * T03: Durasi 30 menit (kurang dari 1 jam).
     * Ekspektasi: minimum 1 jam → Rp 2.000
     */
    public function test_T03_minimum_1_hour(): void
    {
        $entry = Carbon::parse('2025-01-01 08:00:00');
        $exit  = Carbon::parse('2025-01-01 08:30:00');
        $calc  = $this->calculator->calculate($entry, $exit, VehicleCategory::Motor);

        $this->assertEquals(1, $calc['hours']);
        $this->assertEquals(2000, $calc['fee']);
    }
}
