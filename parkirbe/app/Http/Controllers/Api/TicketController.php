<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\VehicleCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\PayTicketRequest;
use App\Http\Requests\Ticket\PreviewFeeRequest;
use App\Http\Resources\ParkingTicketResource;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * TicketController mengelola tiket parkir non-member.
 *
 * POST   /tickets                 → issue (buat tiket baru)
 * GET    /tickets/{token}         → show (detail tiket)
 * GET    /tickets/{token}/preview → preview kalkulasi biaya
 * POST   /tickets/{token}/pay    → pay (proses pembayaran)
 */
class TicketController extends Controller
{
    public function __construct(
        private readonly TicketService $ticketService,
    ) {}

    /**
     * Membuat tiket parkir baru saat kendaraan masuk.
     * Mengembalikan token QR yang akan dicetak/ditampilkan.
     *
     * @return JsonResponse
     */
    public function issue(): JsonResponse
    {
        $ticket = $this->ticketService->issue();

        return response()->json([
            'message' => 'Tiket berhasil dibuat.',
            'ticket'  => new ParkingTicketResource($ticket),
        ], 201);
    }

    /**
     * Mendapatkan detail tiket berdasarkan token.
     *
     * @param string $token
     * @return JsonResponse
     */
    public function show(string $token): JsonResponse
    {
        $ticket = $this->ticketService->findByToken($token);

        return response()->json([
            'ticket' => new ParkingTicketResource($ticket->load('processor')),
        ]);
    }

    /**
     * Preview kalkulasi biaya tanpa menyimpan (untuk UI sebelum konfirmasi).
     *
     * @param PreviewFeeRequest $request
     * @param string            $token
     * @return JsonResponse
     */
    public function preview(PreviewFeeRequest $request, string $token): JsonResponse
    {
        $ticket   = $this->ticketService->findByToken($token);
        $category = VehicleCategory::from($request->string('vehicle_category')->toString());
        $calc     = $this->ticketService->previewFee($ticket, $category);

        return response()->json([
            'token'           => $ticket->token,
            'entry_at'        => $ticket->entry_at->toIso8601String(),
            'hours'           => $calc['hours'],
            'tariff_per_hour' => $calc['tariff_per_hour'],
            'fee'             => $calc['fee'],
        ]);
    }

    /**
     * Memproses pembayaran tiket parkir.
     * Dijalankan oleh petugas setelah scan QR tiket keluar.
     *
     * @param PayTicketRequest $request
     * @param string           $token
     * @return JsonResponse
     */
    public function pay(PayTicketRequest $request, string $token): JsonResponse
    {
        $ticket   = $this->ticketService->findByToken($token);
        $category = VehicleCategory::from($request->string('vehicle_category')->toString());

        $paid = $this->ticketService->pay(
            ticket:       $ticket,
            category:     $category,
            plateNumber:  $request->string('plate_number')->toString(),
            cashReceived: (int) $request->input('cash_received'),
            processedBy:  $request->user(),
        );

        return response()->json([
            'message' => 'Pembayaran berhasil.',
            'ticket'  => new ParkingTicketResource($paid->load('processor')),
        ]);
    }
}
