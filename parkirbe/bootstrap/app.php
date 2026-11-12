<?php

declare(strict_types=1);

use App\Exceptions\Domain\BillAlreadyPaidException;
use App\Exceptions\Domain\DomainException;
use App\Exceptions\Domain\InsufficientCashException;
use App\Exceptions\Domain\InvalidMemberCodeException;
use App\Exceptions\Domain\MemberNotActiveException;
use App\Exceptions\Domain\TicketAlreadyPaidException;
use App\Exceptions\Domain\UnpaidBillException;
use App\Http\Middleware\EnsureSuperAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:      __DIR__ . '/../routes/web.php',
        api:      __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health:   '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias 'superadmin' untuk middleware EnsureSuperAdmin
        $middleware->alias([
            'superadmin' => EnsureSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Semua request ke /api/* dikembalikan sebagai JSON
        $exceptions->shouldRenderJsonWhen(
            fn(Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Handler global untuk semua DomainException
        // Mengubah exception domain menjadi JSON response yang terstruktur
        $exceptions->render(function (DomainException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'error'   => class_basename($e),
                ], $e->getStatusCode());
            }
        });

        // Handler untuk ModelNotFoundException (misal data ID atau token tidak ditemukan)
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $modelName = class_basename($e->getModel());
                $label = match ($modelName) {
                    'ParkingTicket' => 'Tiket parkir',
                    'Member'        => 'Data member',
                    'MemberBill'    => 'Tagihan member',
                    'User'          => 'Data pengguna',
                    default         => 'Data',
                };
                return response()->json([
                    'message' => "{$label} tidak ditemukan dalam sistem.",
                    'error'   => 'NotFound',
                ], 404);
            }
        });
    })->create();
