<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * FonnteService menangani pengiriman pesan WhatsApp via API gateway Fonnte (fonnte.com).
 */
class FonnteService
{
    private string $token;
    private string $apiUrl;

    public function __construct()
    {
        $this->token  = (string) config('services.fonnte.token', env('FONNTE_TOKEN', ''));
        $this->apiUrl = (string) config('services.fonnte.url', env('FONNTE_URL', 'https://api.fonnte.com/send'));
    }

    /**
     * Kirim pesan WhatsApp ke nomor tujuan.
     *
     * @param string $targetPhone Nomor HP tujuan (misal: 08123456789 atau 628123456789)
     * @param string $message     Teks pesan WhatsApp
     * @return array{success: bool, message: string}
     */
    public function sendWhatsAppMessage(string $targetPhone, string $message): array
    {
        $cleanPhone = $this->normalizePhoneNumber($targetPhone);

        if (empty($cleanPhone)) {
            return [
                'success' => false,
                'message' => 'Nomor WhatsApp tujuan kosong atau tidak valid.',
            ];
        }

        // Jika token belum disetel di .env, catat pesan di log (mode simulasi/demo)
        if (empty($this->token) || $this->token === 'your_fonnte_api_token_here') {
            Log::info("[Fonnte WA Simulation] Ke: {$cleanPhone} | Pesan:\n{$message}");
            return [
                'success' => true,
                'message' => 'Pesan WhatsApp tercatat pada sistem log (Mode Simulasi Fonnte).',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->timeout(10)->post($this->apiUrl, [
                'target'      => $cleanPhone,
                'message'     => $message,
                'countryCode' => '62',
            ]);

            $body = $response->json();

            if ($response->successful() && ($body['status'] ?? false)) {
                Log::info("[Fonnte WA Sukses] Ke: {$cleanPhone} | Response: " . json_encode($body));
                return [
                    'success' => true,
                    'message' => 'Pesan WhatsApp berhasil dikirim via Fonnte.',
                ];
            }

            $errMsg = $body['reason'] ?? $body['message'] ?? 'Gagal mengirim pesan WhatsApp.';
            Log::warning("[Fonnte WA Gagal] Ke: {$cleanPhone} | " . $errMsg);
            return [
                'success' => false,
                'message' => $errMsg,
            ];
        } catch (\Throwable $e) {
            Log::error("[Fonnte WA Error] Ke: {$cleanPhone} | Exception: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan koneksi ke server gateway Fonnte: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Normalisasi nomor telepon: hapus karakter non-angka dan ubah format 08xx ke 628xx.
     */
    private function normalizePhoneNumber(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (empty($digits)) return '';

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }

        return $digits;
    }
}

