<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk akun pengguna demo.
 * Membuat 1 super admin dan 2 petugas.
 *
 * PERHATIAN: Untuk production, ganti password dengan yang kuat
 * dan tidak commit ke version control.
 */
class UserSeeder extends Seeder
{
    /**
     * Seed akun pengguna.
     * Menggunakan updateOrCreate agar idempotent (aman dijalankan ulang).
     */
    public function run(): void
    {
        // Super Admin — hanya 1 yang boleh ada
        User::updateOrCreate(
            ['email' => 'superadmin@parkir.test'],
            [
                'name'     => 'Super Admin',
                'username' => 'superadmin',
                'password' => bcrypt('password123'),
                'role'     => UserRole::SuperAdmin,
            ]
        );

        // Petugas demo
        User::updateOrCreate(
            ['email' => 'petugas1@parkir.test'],
            [
                'name'     => 'Petugas Satu',
                'username' => 'petugas1',
                'password' => bcrypt('password123'),
                'role'     => UserRole::Petugas,
            ]
        );

        User::updateOrCreate(
            ['email' => 'petugas2@parkir.test'],
            [
                'name'     => 'Petugas Dua',
                'username' => 'petugas2',
                'password' => bcrypt('password123'),
                'role'     => UserRole::Petugas,
            ]
        );

        $this->command->info('Users seeded: 1 super_admin + 2 petugas');
    }
}
