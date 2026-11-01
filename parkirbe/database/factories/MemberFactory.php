<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\VehicleCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory untuk model Member.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    /**
     * Definisi state default factory.
     */
    public function definition(): array
    {
        $category = fake()->randomElement(VehicleCategory::cases());
        return [
            'member_code'      => 'MBR-' . strtoupper(Str::random(8)),
            'name'             => fake('id_ID')->name(),
            'plate_number'     => strtoupper(fake()->bothify('B ### ???')),
            'vehicle_category' => $category,
            'monthly_fee'      => $category === VehicleCategory::Motor ? 150000 : 200000,
            'is_active'        => true,
            'created_by'       => User::factory(),
        ];
    }
}
