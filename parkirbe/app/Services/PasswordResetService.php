<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * PasswordResetService menangani alur lupa password.
 *
 * CATATAN PRODUKSI: Untuk produksi, reset_token sebaiknya dikirim via email
 * (mis. menggunakan Laravel Mail + Mailgun/SendGrid) dan TIDAK dikembalikan
 * langsung di response API. Implementasi saat ini untuk keperluan demonstrasi.
 */
class PasswordResetService
{
    /** Durasi validitas token dalam menit */
    private const TOKEN_TTL_MINUTES = 15;

    /**
     * Meminta reset password — generate token sekali pakai.
     * Token dikembalikan langsung (untuk demo; produksi: kirim via email).
     *
     * @param string $email
     * @return string|null Token plaintext, atau null jika email tidak ditemukan
     */
    public function requestReset(string $email): ?string
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            return null;
        }

        $token = Str::random(64);

        // Simpan hash token (keamanan: token asli tidak disimpan di DB)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token'      => Hash::make($token),
                'created_at' => now(),
            ]
        );

        return $token;
    }

    /**
     * Mereset password dengan token yang valid.
     *
     * @param string $email
     * @param string $token     Token plaintext dari langkah 1
     * @param string $password  Password baru
     * @return bool             true jika berhasil
     */
    public function reset(string $email, string $token, string $password): bool
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record) {
            return false;
        }

        // Cek apakah token sudah kedaluwarsa
        $createdAt = Carbon::parse($record->created_at);
        if ($createdAt->addMinutes(self::TOKEN_TTL_MINUTES)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return false;
        }

        // Verifikasi token
        if (!Hash::check($token, $record->token)) {
            return false;
        }

        // Update password dan hapus token
        User::where('email', $email)->update(['password' => Hash::make($password)]);
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return true;
    }
}
