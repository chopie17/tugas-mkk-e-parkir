<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * AuthController menangani autentikasi, profil, dan reset password.
 *
 * Endpoint publik: POST /login, POST /forgot-password, POST /reset-password
 * Endpoint terproteksi (Sanctum): POST /logout, GET /me, PUT /me
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService          $authService,
        private readonly PasswordResetService $passwordResetService,
    ) {}

    /**
     * Login petugas/super admin.
     * Mengembalikan Bearer token dan data user.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->string('login')->toString(),
            $request->string('password')->toString()
        );

        if (!$result) {
            return response()->json([
                'message' => 'Email/username atau password salah.',
            ], 401);
        }

        return response()->json([
            'message' => 'Login berhasil.',
            'token'   => $result['token'],
            'user'    => new UserResource($result['user']),
        ]);
    }

    /**
     * Logout — revoke token saat ini.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    /**
     * Mendapatkan data user yang sedang login.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }

    /**
     * Update profil user yang sedang login.
     *
     * @param UpdateProfileRequest $request
     * @return JsonResponse
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user       = $request->user();
        $updateData = $request->only(['name', 'username', 'email']);

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->string('password')->toString());
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user'    => new UserResource($user->fresh()),
        ]);
    }

    /**
     * Langkah 1 — Meminta token reset password.
     * CATATAN: Untuk produksi, token dikirim via email, bukan dikembalikan di response.
     *
     * @param ForgotPasswordRequest $request
     * @return JsonResponse
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $token = $this->passwordResetService->requestReset(
            $request->string('email')->toString()
        );

        // Selalu kembalikan 200 agar tidak membocorkan info akun terdaftar
        return response()->json([
            'message'      => 'Jika email terdaftar, token reset telah dikirim.',
            'reset_token'  => $token, // DEMO ONLY — hapus di produksi
        ]);
    }

    /**
     * Langkah 2 — Reset password dengan token.
     *
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $success = $this->passwordResetService->reset(
            $request->string('email')->toString(),
            $request->string('reset_token')->toString(),
            $request->string('password')->toString()
        );

        if (!$success) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kedaluwarsa.',
            ], 422);
        }

        return response()->json(['message' => 'Password berhasil direset.']);
    }
}
