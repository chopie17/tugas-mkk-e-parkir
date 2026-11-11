<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\TicketStatus;
use App\Enums\VehicleCategory;
use App\Http\Controllers\Controller;
use App\Http\Resources\MemberScanResource;
use App\Http\Resources\ParkingTicketResource;
use App\Models\ParkingTicket;
use App\Models\User;
use App\Services\MemberScanService;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GateController menangani operasional terminal gerbang mandiri (Gate Kiosk):
 * 1. Ambil tiket masuk non-member (tanpa scan di awal, auto-download & buka gerbang)
 * 2. Scan / input manual kartu member (Anti-Passback: Masuk hanya jika OUT, Keluar hanya jika IN)
 * 3. Scan tiket non-member saat keluar (hanya bisa 1x pakai, tolak jika sudah keluar)
 * 4. Penyelesaian tiket keluar non-member
 */
class GateController extends Controller
{
    public function __construct(
        private readonly TicketService     $ticketService,
        private readonly MemberScanService $memberScanService,
    ) {}

    /**
     * 1. Ambil Tiket Masuk (Non-Member) di Kiosk Gerbang Masuk.
     * Tidak memerlukan login petugas. Menghasilkan token dan waktu masuk.
     */
    public function dispenseTicket(): JsonResponse
    {
        $ticket = $this->ticketService->issue();

        return response()->json([
            'success' => true,
            'message' => 'Tiket parkir berhasil diterbitkan. Silakan ambil karcis dan masuk.',
            'ticket'  => [
                'id'       => $ticket->id,
                'token'    => $ticket->token,
                'entry_at' => $ticket->entry_at->toIso8601String(),
                'status'   => $ticket->status->value,
            ],
            'gate'    => [
                'action'  => 'open',
                'gate_id' => 'GATE-IN-01',
            ],
        ], 201);
    }

    /**
     * 2. Scan atau Input Manual Kartu Member (Gate In & Gate Out).
     * Menerapkan logika Anti-Passback.
     */
    public function scanMember(Request $request): JsonResponse
    {
        $request->validate([
            'member_code' => 'required|string|max:50',
            'direction'   => 'nullable|string|in:in,out',
        ]);

        $direction = $request->input('direction', 'in');

        $result = $this->memberScanService->scan(
            code:      $request->string('member_code')->toString(),
            direction: $direction,
        );

        $statusCode = $result['allowed'] ? 200 : 422;

        return response()->json([
            'allowed'   => $result['allowed'],
            'reason'    => $result['reason'],
            'direction' => $result['direction'],
            'gate'      => [
                'action'  => $result['allowed'] ? 'open' : 'hold',
                'gate_id' => $direction === 'in' ? 'GATE-IN-01' : 'GATE-OUT-01',
            ],
            'member'    => $result['member'] ? [
                'id'               => $result['member']->id,
                'member_code'      => $result['member']->member_code,
                'name'             => $result['member']->name,
                'plate_number'     => $result['member']->plate_number,
                'vehicle_category' => $result['member']->vehicle_category->value,
                'parking_status'   => $result['member']->parking_status ?? 'out',
            ] : null,
        ], $statusCode);
    }

    /**
     * 3. Scan Tiket Keluar Non-Member (Gate Out).
     * Memastikan tiket hanya bisa digunakan 1 kali keluar.
     */
    public function scanExitTicket(Request $request): JsonResponse
    {
        $request->validate([
            'token'            => 'required|string|max:10',
            'vehicle_category' => 'nullable|string|in:motor,mobil',
        ]);

        $token  = strtoupper(trim($request->string('token')->toString()));
        $ticket = ParkingTicket::where('token', $token)->first();

        // 1. Tiket tidak ditemukan
        if (!$ticket) {
            return response()->json([
                'allowed' => false,
                'reason'  => 'Tiket tidak ditemukan dalam sistem.',
                'ticket'  => null,
            ], 404);
        }

        // 2. Tiket sudah pernah digunakan keluar sebelumnya (Status PAID)
        if ($ticket->isPaid()) {
            return response()->json([
                'allowed' => false,
                'reason'  => 'Akses Keluar Ditolak: Tiket ' . $ticket->token . ' sudah pernah digunakan keluar pada ' . ($ticket->exit_at ? $ticket->exit_at->format('d/m/Y H:i') : '-') . ' WIB. Tiket sekali pakai sudah tidak berlaku lagi.',
                'ticket'  => [
                    'token'   => $ticket->token,
                    'status'  => 'paid',
                    'exit_at' => $ticket->exit_at?->toIso8601String(),
                ],
            ], 422);
        }

        // 3. Tiket masih aktif — Hitung perkiraan biaya
        $category = VehicleCategory::tryFrom($request->input('vehicle_category', 'motor')) ?? VehicleCategory::Motor;
        $calc     = $this->ticketService->previewFee($ticket, $category);

        return response()->json([
            'allowed' => true,
            'reason'  => 'Tiket valid. Silakan selesaikan pembayaran untuk membuka gerbang keluar.',
            'ticket'  => [
                'token'           => $ticket->token,
                'status'          => 'active',
                'entry_at'        => $ticket->entry_at->toIso8601String(),
                'hours'           => $calc['hours'],
                'tariff_per_hour' => $calc['tariff_per_hour'],
                'fee'             => $calc['fee'],
            ],
        ]);
    }

    /**
     * 4. Selesaikan Pembayaran & Buka Gerbang Keluar Non-Member.
     */
    public function payExitTicket(Request $request): JsonResponse
    {
        $request->validate([
            'token'            => 'required|string|max:10',
            'vehicle_category' => 'required|string|in:motor,mobil',
            'plate_number'     => 'required|string|max:20',
            'cash_received'    => 'required|integer|min:0',
        ]);

        $token    = strtoupper(trim($request->string('token')->toString()));
        $ticket   = $this->ticketService->findByToken($token);
        $category = VehicleCategory::from($request->string('vehicle_category')->toString());

        // Gunakan user login jika ada, atau fallback ke akun sistem (ID 1) untuk kiosk otomatis
        $operator = $request->user() ?? User::first();

        $paid = $this->ticketService->pay(
            ticket:       $ticket,
            category:     $category,
            plateNumber:  $request->string('plate_number')->toString(),
            cashReceived: (int) $request->input('cash_received'),
            processedBy:  $operator,
        );

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran tiket berhasil. Gerbang keluar dibuka.',
            'gate'    => [
                'action'  => 'open',
                'gate_id' => 'GATE-OUT-01',
            ],
            'ticket'  => new ParkingTicketResource($paid->load('processor')),
        ]);
    }
}

