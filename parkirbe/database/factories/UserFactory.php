<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory untuk model User (Petugas dan Super Admin).
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Definisi state default factory.
     */
    public function definition(): array
    {
        return [
            'name'     => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email'    => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'role'     => UserRole::Petugas,
        ];
    }

    /** State untuk membuat user sebagai petugas */
    public function petugas(): static
    {
        return $this->state(['role' => UserRole::Petugas]);
    }

    /** State untuk membuat user sebagai super admin */
    public function superAdmin(): static
    {
        return $this->state(['role' => UserRole::SuperAdmin]);
    }
}
