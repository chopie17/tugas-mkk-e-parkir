<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk membatasi akses hanya ke Super Admin.
 * Digunakan pada route yang butuh hak akses tinggi
 * (manajemen user, tarif, generate tagihan).
 */
class EnsureSuperAdmin
{
    /**
     * Cek apakah user yang login memiliki role super_admin.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role !== UserRole::SuperAdmin) {
            return response()->json([
                'message' => 'Akses ditolak. Hanya Super Admin yang dapat mengakses fitur ini.',
            ], 403);
        }

        return $next($request);
    }
}
