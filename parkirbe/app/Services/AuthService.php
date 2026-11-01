<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * AuthService menangani autentikasi pengguna.
 * Field 'login' bisa berisi email ATAU username.
 */
class AuthService
{
    /**
     * Mencoba autentikasi user dengan login (email/username) dan password.
     *
     * @param string $login    Email atau username
     * @param string $password Password plaintext
     * @return bool            true jika berhasil
     */
    public function attempt(string $login, string $password): bool
    {
        $user = $this->findUserByLogin($login);
        if (!$user) {
            return false;
        }
        return Hash::check($password, $user->password);
    }

    /**
     * Login user dan kembalikan token Sanctum + data user.
     *
     * @param string $login
     * @param string $password
     * @return array{token: string, user: User}|null
     */
    public function login(string $login, string $password): ?array
    {
        $user = $this->findUserByLogin($login);
        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        // Revoke token lama agar tidak menumpuk
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return ['token' => $token, 'user' => $user];
    }

    /**
     * Mencari user berdasarkan email atau username.
     *
     * @param string $login
     * @return User|null
     */
    private function findUserByLogin(string $login): ?User
    {
        return User::where('email', $login)
            ->orWhere('username', $login)
            ->first();
    }
}
