<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\BillStatus;
use App\Enums\VehicleCategory;
use App\Models\Member;
use App\Models\MemberBill;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeder untuk data member demo.
 * Membuat 3 member dengan variasi status tagihan bulan berjalan.
 */
class MemberSeeder extends Seeder
{
    /**
     * Seed data member + tagihan bulan berjalan.
     */
    public function run(): void
    {
        $petugas1 = User::where('username', 'petugas1')->first();
        $petugas2 = User::where('username', 'petugas2')->first();
        $period   = now()->startOfMonth()->toDateString();

        // Member 1: Motor, LUNAS bulan ini
        $member1 = Member::updateOrCreate(
            ['member_code' => 'MBR-DEMO0001'],
            [
                'name'             => 'Budi Santoso',
                'plate_number'     => 'B 1234 ABC',
                'vehicle_category' => VehicleCategory::Motor,
                'monthly_fee'      => 150000,
                'is_active'        => true,
                'created_by'       => $petugas1->id,
            ]
        );
        MemberBill::updateOrCreate(
            ['member_id' => $member1->id, 'period' => $period],
            [
                'amount'         => 150000,
                'status'         => BillStatus::Lunas,
                'cash_received'  => 200000,
                'change_amount'  => 50000,
                'paid_at'        => now()->subDays(3),
                'processed_by'   => $petugas1->id,
                'invoice_number' => 'INV-M-' . now()->format('Ym') . '-0001',
            ]
        );

        // Member 2: Mobil, BELUM LUNAS bulan ini
        $member2 = Member::updateOrCreate(
            ['member_code' => 'MBR-DEMO0002'],
            [
                'name'             => 'Siti Rahayu',
                'plate_number'     => 'B 5678 DEF',
                'vehicle_category' => VehicleCategory::Mobil,
                'monthly_fee'      => 200000,
                'is_active'        => true,
                'created_by'       => $petugas1->id,
            ]
        );
        MemberBill::updateOrCreate(
            ['member_id' => $member2->id, 'period' => $period],
            ['amount' => 200000, 'status' => BillStatus::BelumLunas]
        );

        // Member 3: Motor, BELUM LUNAS (punya tagihan bulan lalu juga)
        $member3 = Member::updateOrCreate(
            ['member_code' => 'MBR-DEMO0003'],
            [
                'name'             => 'Ahmad Fauzi',
                'plate_number'     => 'B 9999 GHI',
                'vehicle_category' => VehicleCategory::Motor,
                'monthly_fee'      => 150000,
                'is_active'        => true,
                'created_by'       => $petugas2->id,
            ]
        );
        // Tagihan bulan lalu (belum lunas)
        $lastMonthPeriod = now()->subMonth()->startOfMonth()->toDateString();
        MemberBill::updateOrCreate(
            ['member_id' => $member3->id, 'period' => $lastMonthPeriod],
            ['amount' => 150000, 'status' => BillStatus::BelumLunas]
        );
        // Tagihan bulan ini (belum lunas)
        MemberBill::updateOrCreate(
            ['member_id' => $member3->id, 'period' => $period],
            ['amount' => 150000, 'status' => BillStatus::BelumLunas]
        );

        $this->command->info('Members seeded: 3 members (1 lunas, 2 belum lunas)');
    }
}
