<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TariffType;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Enums\VehicleCategory;
use App\Models\ParkingTicket;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature test untuk siklus tiket parkir non-member.
 *
 * Skenario:
 * - T04: Petugas buat tiket → 201, token ada
 * - T05: Preview biaya tiket aktif → fee = hours × tariff
 * - T06: Bayar tiket berhasil → status paid, kembalian benar
 * - T07: Bayar tiket yang sudah paid → 409
 * - T08: Bayar tiket dengan uang kurang → 422
 */
class TicketApiTest extends TestCase
{
    use RefreshDatabase;

    private User $petugas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->petugas = User::factory()->petugas()->create();
        Tariff::create(['type' => TariffType::NonMemberHourly, 'vehicle_category' => VehicleCategory::Motor, 'amount' => 2000]);
        Tariff::create(['type' => TariffType::NonMemberHourly, 'vehicle_category' => VehicleCategory::Mobil, 'amount' => 5000]);
    }

    /**
     * T04: POST /api/tickets → buat tiket baru.
     */
    public function test_T04_issue_ticket(): void
    {
        $response = $this->actingAs($this->petugas, 'sanctum')
            ->postJson('/api/tickets');

        $response->assertStatus(201)
            ->assertJsonPath('ticket.status', 'active')
            ->assertJsonStructure(['ticket' => ['token', 'entry_at', 'status']]);
    }

    /**
     * T05: GET /api/tickets/{token}/preview → preview biaya.
     * Freeze time lalu set entry_at tepat 120 menit sebelum now.
     */
    public function test_T05_preview_fee(): void
    {
        $now = \Carbon\Carbon::create(2025, 6, 1, 10, 0, 0);
        \Carbon\Carbon::setTestNow($now);

        $ticket = ParkingTicket::factory()->create([
            'entry_at' => \Carbon\Carbon::create(2025, 6, 1, 8, 0, 0),
            'status'   => TicketStatus::Active,
        ]);

        $response = $this->actingAs($this->petugas, 'sanctum')
            ->getJson("/api/tickets/{$ticket->token}/preview?vehicle_category=motor");

        \Carbon\Carbon::setTestNow(); // Reset

        $response->assertOk()
            ->assertJsonPath('hours', 2)
            ->assertJsonPath('fee', 4000)
            ->assertJsonPath('tariff_per_hour', 2000);
    }

    /**
     * T06: POST /api/tickets/{token}/pay → bayar tiket berhasil.
     * Gunakan waktu fixed untuk 2 jam yang tepat.
     */
    public function test_T06_pay_ticket_success(): void
    {
        $now = \Carbon\Carbon::create(2025, 6, 1, 10, 0, 0);
        \Carbon\Carbon::setTestNow($now);

        $ticket = ParkingTicket::factory()->create([
            'entry_at' => \Carbon\Carbon::create(2025, 6, 1, 8, 0, 0),
            'status'   => TicketStatus::Active,
        ]);

        $response = $this->actingAs($this->petugas, 'sanctum')
            ->postJson("/api/tickets/{$ticket->token}/pay", [
                'vehicle_category' => 'motor',
                'plate_number'     => 'B 1234 ABC',
                'cash_received'    => 10000,
            ]);

        \Carbon\Carbon::setTestNow(); // Reset

        $response->assertOk()
            ->assertJsonPath('ticket.status', 'paid')
            ->assertJsonPath('ticket.fee', 4000)
            ->assertJsonPath('ticket.change_amount', 6000);
    }

    /**
     * T07: POST /api/tickets/{token}/pay tiket sudah paid → 409 Conflict.
     */
    public function test_T07_pay_already_paid_ticket_returns_409(): void
    {
        $ticket = ParkingTicket::factory()->paid()->create();

        $response = $this->actingAs($this->petugas, 'sanctum')
            ->postJson("/api/tickets/{$ticket->token}/pay", [
                'vehicle_category' => 'motor',
                'plate_number'     => 'B 9999 ZZZ',
                'cash_received'    => 10000,
            ]);

        $response->assertStatus(409);
    }

    /**
     * T08: POST /api/tickets/{token}/pay uang kurang → 422.
     */
    public function test_T08_insufficient_cash_returns_422(): void
    {
        $ticket = ParkingTicket::factory()->create([
            'entry_at' => now()->subHours(3),
            'status'   => TicketStatus::Active,
        ]);

        $response = $this->actingAs($this->petugas, 'sanctum')
            ->postJson("/api/tickets/{$ticket->token}/pay", [
                'vehicle_category' => 'motor',
                'plate_number'     => 'B 1111 ABC',
                'cash_received'    => 100, // Jauh di bawah fee (6000)
            ]);

        $response->assertStatus(422);
    }
}
