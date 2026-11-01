<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\BillStatus;
use App\Enums\TariffType;
use App\Enums\UserRole;
use App\Enums\VehicleCategory;
use App\Models\Member;
use App\Models\MemberBill;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature test untuk tagihan member dan scan kartu.
 *
 * Skenario:
 * - T09: Bayar tagihan berhasil → status lunas, kembalian benar, invoice ada
 * - T10: Bayar tagihan yang sudah lunas → 409
 * - T11: Bayar tagihan dengan uang kurang → 422
 * - T12: Scan kartu member OK → allowed=true
 * - T13: Scan kartu member dengan tagihan belum lunas → allowed=false
 * - T14: Scan kartu member tidak aktif → allowed=false
 */
class MemberBillApiTest extends TestCase
{
    use RefreshDatabase;

    private User $petugas;
    private Member $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->petugas = User::factory()->petugas()->create();
        Tariff::create(['type' => TariffType::MemberMonthly, 'vehicle_category' => VehicleCategory::Motor, 'amount' => 150000]);
        Tariff::create(['type' => TariffType::MemberMonthly, 'vehicle_category' => VehicleCategory::Mobil, 'amount' => 200000]);
        Tariff::create(['type' => TariffType::NonMemberHourly, 'vehicle_category' => VehicleCategory::Motor, 'amount' => 2000]);
        Tariff::create(['type' => TariffType::NonMemberHourly, 'vehicle_category' => VehicleCategory::Mobil, 'amount' => 5000]);

        $this->member = Member::factory()->create([
            'vehicle_category' => VehicleCategory::Motor,
            'monthly_fee'      => 150000,
            'is_active'        => true,
            'created_by'       => $this->petugas->id,
        ]);
    }

    /**
     * T09: POST /api/bills/{bill}/pay → lunas, kembalian benar.
     */
    public function test_T09_pay_bill_success(): void
    {
        $bill = MemberBill::factory()->create([
            'member_id' => $this->member->id,
            'amount'    => 150000,
            'status'    => BillStatus::BelumLunas,
        ]);

        $response = $this->actingAs($this->petugas, 'sanctum')
            ->postJson("/api/bills/{$bill->id}/pay", [
                'cash_received' => 200000,
            ]);

        $response->assertOk()
            ->assertJsonPath('bill.status', 'lunas')
            ->assertJsonPath('bill.change_amount', 50000);

        $this->assertNotNull($response->json('bill.invoice_number'));
    }

    /**
     * T10: Bayar tagihan yang sudah lunas → 409 Conflict.
     */
    public function test_T10_pay_already_paid_bill_returns_409(): void
    {
        $bill = MemberBill::factory()->lunas()->create([
            'member_id' => $this->member->id,
        ]);

        $response = $this->actingAs($this->petugas, 'sanctum')
            ->postJson("/api/bills/{$bill->id}/pay", [
                'cash_received' => 200000,
            ]);

        $response->assertStatus(409);
    }

    /**
     * T11: Bayar tagihan dengan uang kurang → 422.
     */
    public function test_T11_insufficient_cash_for_bill_returns_422(): void
    {
        $bill = MemberBill::factory()->create([
            'member_id' => $this->member->id,
            'amount'    => 150000,
            'status'    => BillStatus::BelumLunas,
        ]);

        $response = $this->actingAs($this->petugas, 'sanctum')
            ->postJson("/api/bills/{$bill->id}/pay", [
                'cash_received' => 50000, // Kurang dari 150000
            ]);

        $response->assertStatus(422);
    }

    /**
     * T12: Scan kartu member tanpa tagihan → allowed=true.
     */
    public function test_T12_scan_member_ok(): void
    {
        // Tidak ada tagihan belum lunas
        $response = $this->actingAs($this->petugas, 'sanctum')
            ->postJson('/api/members/scan', [
                'member_code' => $this->member->member_code,
            ]);

        $response->assertOk()
            ->assertJsonPath('allowed', true);
    }

    /**
     * T13: Scan kartu member dengan tagihan belum lunas → allowed=false.
     */
    public function test_T13_scan_member_with_unpaid_bill(): void
    {
        MemberBill::factory()->create([
            'member_id' => $this->member->id,
            'amount'    => 150000,
            'status'    => BillStatus::BelumLunas,
        ]);

        $response = $this->actingAs($this->petugas, 'sanctum')
            ->postJson('/api/members/scan', [
                'member_code' => $this->member->member_code,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('allowed', false);
    }

    /**
     * T14: Scan kartu member yang tidak aktif → allowed=false.
     */
    public function test_T14_scan_inactive_member(): void
    {
        $this->member->update(['is_active' => false]);

        $response = $this->actingAs($this->petugas, 'sanctum')
            ->postJson('/api/members/scan', [
                'member_code' => $this->member->member_code,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('allowed', false);
    }
}
