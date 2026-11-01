<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BillController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\MemberScanController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\TariffController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| E-Parkir Plaza Andalas — API Routes
|--------------------------------------------------------------------------
|
| Semua route diprefix /api secara otomatis oleh Laravel.
| Autentikasi menggunakan Laravel Sanctum (Bearer token).
|
| Role:
|   - petugas     : akses tiket, scan member, bayar tagihan, laporan diri sendiri
|   - super_admin : semua akses petugas + manajemen user, tarif, laporan semua
|
*/

// ─── Route Publik (Tanpa Autentikasi) ────────────────────────────────────────

/** Tarif parkir — publik agar bisa tampil di display gate */
Route::get('/tariffs', [TariffController::class, 'index']);

/** Autentikasi */
Route::post('/login',          [AuthController::class, 'login']);
Route::post('/forgot-password',[AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// ─── Route Terproteksi (Sanctum) ──────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // ── Autentikasi & Profil ──────────────────────────────────────────────
    Route::post('/logout',   [AuthController::class, 'logout']);
    Route::get('/me',        [AuthController::class, 'me']);
    Route::put('/me',        [AuthController::class, 'updateProfile']);

    // ── Tiket Parkir Non-Member ───────────────────────────────────────────
    Route::prefix('tickets')->group(function () {
        Route::post('/',               [TicketController::class, 'issue']);   // Cetak tiket masuk
        Route::get('/{token}',         [TicketController::class, 'show']);    // Detail tiket by token
        Route::get('/{token}/preview', [TicketController::class, 'preview']); // Preview biaya
        Route::post('/{token}/pay',    [TicketController::class, 'pay']);     // Bayar tiket
    });

    // ── Member ───────────────────────────────────────────────────────────
    Route::post('/members/scan', [MemberScanController::class, 'scan']); // Scan QR member
    Route::apiResource('members', MemberController::class);
    Route::get('/members/{member}/bills', [MemberController::class, 'bills']); // Tagihan member

    // ── Tagihan Member ────────────────────────────────────────────────────
    Route::get('/bills',              [BillController::class, 'index']); // Daftar tagihan
    Route::get('/bills/{bill}',       [BillController::class, 'show']);  // Detail tagihan
    Route::post('/bills/{bill}/pay',  [BillController::class, 'pay']);   // Bayar tagihan

    // ── Laporan Transaksi ─────────────────────────────────────────────────
    Route::prefix('reports')->group(function () {
        Route::get('/non-member', [ReportController::class, 'nonMember']);
        Route::get('/member',     [ReportController::class, 'member']);
        Route::get('/summary',    [ReportController::class, 'summary']);
    });

    // ── Super Admin Only ──────────────────────────────────────────────────
    Route::middleware('superadmin')->group(function () {
        Route::apiResource('users', UserController::class); // Manajemen pengguna
        Route::put('/tariffs/{tariff}', [TariffController::class, 'update']); // Update tarif
        Route::post('/bills/generate',  [BillController::class, 'generate']); // Generate tagihan bulanan
    });
});
