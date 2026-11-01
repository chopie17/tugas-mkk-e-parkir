<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TicketStatus;
use App\Enums\VehicleCategory;
use App\Models\ParkingTicket;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk data tiket parkir non-member demo.
 * Membuat beberapa tiket dengan status sudah bayar agar laporan tidak kosong.
 */
class ParkingTicketSeeder extends Seeder
{
    /**
     * Seed tiket parkir demo.
     */
    public function run(): void
    {
        $petugas1 = User::where('username', 'petugas1')->first();
        $petugas2 = User::where('username', 'petugas2')->first();

        $tickets = [
            [
                'token'            => 'A1B2C3',
                'entry_at'         => now()->subHours(3),
                'exit_at'          => now()->subHours(1),
                'status'           => TicketStatus::Paid,
                'vehicle_category' => VehicleCategory::Motor,
                'plate_number'     => 'B 1111 AAA',
                'duration_hours'   => 2,
                'fee'              => 4000,
                'cash_received'    => 10000,
                'change_amount'    => 6000,
                'processed_by'     => $petugas1->id,
                'invoice_number'   => 'INV-NM-' . now()->format('Ymd') . '-0001',
            ],
            [
                'token'            => 'D4E5F6',
                'entry_at'         => now()->subHours(5),
                'exit_at'          => now()->subHours(2),
                'status'           => TicketStatus::Paid,
                'vehicle_category' => VehicleCategory::Mobil,
                'plate_number'     => 'B 2222 BBB',
                'duration_hours'   => 3,
                'fee'              => 15000,
                'cash_received'    => 20000,
                'change_amount'    => 5000,
                'processed_by'     => $petugas1->id,
                'invoice_number'   => 'INV-NM-' . now()->format('Ymd') . '-0002',
            ],
            [
                'token'            => 'G7H8I9',
                'entry_at'         => now()->subHours(2),
                'exit_at'          => now()->subMinutes(30),
                'status'           => TicketStatus::Paid,
                'vehicle_category' => VehicleCategory::Motor,
                'plate_number'     => 'B 3333 CCC',
                'duration_hours'   => 2,
                'fee'              => 4000,
                'cash_received'    => 5000,
                'change_amount'    => 1000,
                'processed_by'     => $petugas2->id,
                'invoice_number'   => 'INV-NM-' . now()->format('Ymd') . '-0003',
            ],
            // Tiket aktif (belum bayar)
            [
                'token'    => 'J1K2L3',
                'entry_at' => now()->subMinutes(45),
                'status'   => TicketStatus::Active,
            ],
        ];

        foreach ($tickets as $ticket) {
            ParkingTicket::updateOrCreate(
                ['token' => $ticket['token']],
                $ticket
            );
        }

        $this->command->info('Parking tickets seeded: 3 paid + 1 active');
    }
}
