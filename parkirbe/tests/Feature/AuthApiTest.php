<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature test untuk autentikasi.
 */
class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Login berhasil dengan email + password yang benar.
     */
    public function test_login_success_with_email(): void
    {
        $user = User::factory()->petugas()->create([
            'email'    => 'petugas@test.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/login', [
            'login'    => 'petugas@test.com',
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'email', 'role']]);
    }

    /**
     * Login berhasil dengan username.
     */
    public function test_login_success_with_username(): void
    {
        $user = User::factory()->petugas()->create([
            'username' => 'petugas_demo',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/login', [
            'login'    => 'petugas_demo',
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token']);
    }

    /**
     * Login gagal password salah → 401.
     */
    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->petugas()->create([
            'email'    => 'petugas@test.com',
            'password' => bcrypt('correct'),
        ]);

        $response = $this->postJson('/api/login', [
            'login'    => 'petugas@test.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Super Admin tidak bisa diakses oleh Petugas biasa.
     */
    public function test_petugas_cannot_access_super_admin_routes(): void
    {
        $petugas = User::factory()->petugas()->create();

        $response = $this->actingAs($petugas, 'sanctum')
            ->getJson('/api/users');

        $response->assertStatus(403);
    }
}
