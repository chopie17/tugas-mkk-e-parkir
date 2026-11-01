<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BillStatus;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory untuk model MemberBill.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MemberBill>
 */
class MemberBillFactory extends Factory
{
    /**
     * Definisi state default factory.
     */
    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'period'    => now()->startOfMonth()->toDateString(),
            'amount'    => 150000,
            'status'    => BillStatus::BelumLunas,
        ];
    }

    /** State tagihan sudah lunas */
    public function lunas(): static
    {
        return $this->state([
            'status'         => BillStatus::Lunas,
            'cash_received'  => 200000,
            'change_amount'  => 50000,
            'paid_at'        => now(),
        ]);
    }
}
