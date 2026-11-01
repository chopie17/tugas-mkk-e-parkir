<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder — titik masuk semua seeder.
 * Urutan penting karena ada FK dependency antar tabel.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Jalankan semua seeder dalam urutan yang benar.
     */
    public function run(): void
    {
        $this->call([
            TariffSeeder::class,        // 1. Tarif (tidak ada FK)
            UserSeeder::class,          // 2. User (tidak ada FK)
            MemberSeeder::class,        // 3. Member + MemberBill (FK ke users)
            ParkingTicketSeeder::class, // 4. ParkingTicket (FK ke users)
        ]);

        $this->command->info('All seeders completed successfully!');
    }
}
