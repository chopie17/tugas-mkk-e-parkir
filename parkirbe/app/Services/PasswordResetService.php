<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\PasswordResetOtpMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * PasswordResetService menangani alur lupa password menggunakan OTP 6 digit via Email (Mailtrap).
 */
class PasswordResetService
{
    /** Durasi validitas OTP dalam menit */
    private const TOKEN_TTL_MINUTES = 15;

    /**
     * Meminta reset password — generate OTP 6 digit angka dan kirim ke email petugas.
     *
     * @param string $email
     * @return string|null OTP plaintext untuk keperluan testing / logging
     */
    public function requestReset(string $email): ?string
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            return null;
        }

        // Generate 6 digit OTP angka
        $otp = (string) random_int(100000, 999999);

        // Simpan hash OTP ke database
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token'      => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        // Catat di log untuk kemudahan pengujian lokal
        Log::info("[Lupa Password] OTP untuk {$email}: {$otp}");

        // Kirim email via Mailtrap SMTP
        try {
            Mail::to($email)->send(new PasswordResetOtpMail($user->name, $otp, self::TOKEN_TTL_MINUTES));
        } catch (\Throwable $e) {
            Log::error("[Lupa Password] Gagal mengirim email OTP ke {$email}: " . $e->getMessage());
        }

        return $otp;
    }

    /**
     * Mereset password dengan memverifikasi kode OTP 6 digit.
     *
     * @param string $email
     * @param string $otp       Kode OTP yang diinput pengguna
     * @param string $password  Password baru
     * @return bool             true jika berhasil
     */
    public function reset(string $email, string $otp, string $password): bool
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record) {
            return false;
        }

        // Cek apakah OTP sudah kedaluwarsa
        $createdAt = Carbon::parse($record->created_at);
        if ($createdAt->addMinutes(self::TOKEN_TTL_MINUTES)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return false;
        }

        // Verifikasi kode OTP
        if (!Hash::check(trim($otp), $record->token)) {
            return false;
        }

        // Update password dan hapus record OTP
        User::where('email', $email)->update(['password' => Hash::make($password)]);
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        Log::info("[Lupa Password] Password untuk {$email} berhasil diperbarui.");

        return true;
    }
}
