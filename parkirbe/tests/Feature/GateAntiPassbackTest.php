<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\VehicleCategory;
use App\Models\Member;
use App\Models\ParkingTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GateAntiPassbackTest extends TestCase
{
    use RefreshDatabase;

    private Member $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\TariffSeeder::class);
        $user = User::factory()->create();

        $this->member = Member::create([
            'member_code'      => 'MBR-TEST0001',
            'name'             => 'Test Driver',
            'plate_number'     => 'BA 1234 XY',
            'vehicle_category' => VehicleCategory::Mobil,
            'monthly_fee'      => 100000,
            'is_active'        => true,
            'parking_status'   => 'out',
            'created_by'       => $user->id,
        ]);
    }

    public function test_non_member_can_dispense_ticket_at_entrance(): void
    {
        $response = $this->postJson('/api/gate/tickets/dispense');

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('gate.action', 'open');

        $token = $response->json('ticket.token');
        $this->assertDatabaseHas('parking_tickets', [
            'token'  => $token,
            'status' => 'active',
        ]);
    }

    public function test_member_can_scan_in_when_out(): void
    {
        $response = $this->postJson('/api/gate/members/scan', [
            'member_code' => $this->member->member_code,
            'direction'   => 'in',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('allowed', true)
            ->assertJsonPath('gate.action', 'open');

        $this->assertEquals('in', $this->member->fresh()->parking_status);
    }

    public function test_member_anti_passback_prevents_double_entry(): void
    {
        // First entry -> allowed
        $this->postJson('/api/gate/members/scan', [
            'member_code' => $this->member->member_code,
            'direction'   => 'in',
        ])->assertStatus(200);

        // Second entry while still inside -> denied!
        $response = $this->postJson('/api/gate/members/scan', [
            'member_code' => $this->member->member_code,
            'direction'   => 'in',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('allowed', false)
            ->assertJsonPath('gate.action', 'hold');

        $this->assertStringContainsString('Anti-Passback', $response->json('reason'));
    }

    public function test_member_can_scan_out_after_entry(): void
    {
        // Entry first
        $this->member->update(['parking_status' => 'in']);

        // Exit -> allowed
        $response = $this->postJson('/api/gate/members/scan', [
            'member_code' => $this->member->member_code,
            'direction'   => 'out',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('allowed', true)
            ->assertJsonPath('gate.action', 'open');

        $this->assertEquals('out', $this->member->fresh()->parking_status);
    }

    public function test_member_cannot_scan_out_if_already_out(): void
    {
        // Status is out
        $this->assertEquals('out', $this->member->parking_status);

        $response = $this->postJson('/api/gate/members/scan', [
            'member_code' => $this->member->member_code,
            'direction'   => 'out',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('allowed', false)
            ->assertJsonPath('gate.action', 'hold');

        $this->assertStringContainsString('belum tercatat masuk', $response->json('reason'));
    }

    public function test_non_member_ticket_cannot_be_reused_after_exit(): void
    {
        // Issue ticket
        $ticket = ParkingTicket::create([
            'token'    => 'ABC999',
            'entry_at' => now()->subHours(2),
            'status'   => 'active',
        ]);

        // 1. Scan exit when active -> allowed
        $resScan = $this->postJson('/api/gate/tickets/scan-exit', [
            'token'            => $ticket->token,
            'vehicle_category' => 'mobil',
        ]);

        $resScan->assertStatus(200)
            ->assertJsonPath('allowed', true);

        // 2. Pay ticket to exit
        $resPay = $this->postJson('/api/gate/tickets/pay-exit', [
            'token'            => $ticket->token,
            'vehicle_category' => 'mobil',
            'plate_number'     => 'BA 8888 ZZ',
            'cash_received'    => 50000,
        ]);

        $resPay->assertStatus(200)
            ->assertJsonPath('success', true);

        // 3. Try to scan exit again -> MUST BE DENIED!
        $resReplay = $this->postJson('/api/gate/tickets/scan-exit', [
            'token' => $ticket->token,
        ]);

        $resReplay->assertStatus(422)
            ->assertJsonPath('allowed', false);

        $this->assertStringContainsString('sudah pernah digunakan keluar', $resReplay->json('reason'));
    }
}

