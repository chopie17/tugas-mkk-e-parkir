<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TicketStatus;
use App\Enums\VehicleCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory untuk model ParkingTicket.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ParkingTicket>
 */
class ParkingTicketFactory extends Factory
{
    /**
     * Definisi state default factory.
     */
    public function definition(): array
    {
        return [
            'token'    => strtoupper(Str::random(6)),
            'entry_at' => now()->subHours(fake()->numberBetween(1, 8)),
            'status'   => TicketStatus::Active,
        ];
    }

    /** State tiket sudah dibayar */
    public function paid(): static
    {
        $entryAt  = now()->subHours(fake()->numberBetween(2, 10));
        $exitAt   = $entryAt->copy()->addHours(fake()->numberBetween(1, 5));
        $category = fake()->randomElement(VehicleCategory::cases());
        $hours    = (int) ceil($entryAt->diffInMinutes($exitAt) / 60);
        $tariff   = $category === VehicleCategory::Motor ? 2000 : 5000;
        $fee      = $hours * $tariff;
        return $this->state([
            'exit_at'          => $exitAt,
            'status'           => TicketStatus::Paid,
            'vehicle_category' => $category,
            'plate_number'     => strtoupper(fake()->bothify('B ### ???')),
            'duration_hours'   => $hours,
            'fee'              => $fee,
            'cash_received'    => $fee + fake()->numberBetween(0, 50000),
            'change_amount'    => fake()->numberBetween(0, 50000),
        ]);
    }
}
