<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(Response::HTTP_UNAUTHORIZED, 'Unauthenticated.');
        }

        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $r) {
                $trimmed = trim($r);
                if ($trimmed !== '') {
                    $allowedRoles[] = strtolower($trimmed);
                }
            }
        }

        $userRoleValue = $user->role instanceof UserRole
            ? $user->role->value
            : strtolower((string) $user->role);

        if (! in_array($userRoleValue, $allowedRoles, true)) {
            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman atau aksi ini.');
        }

        return $next($request);
    }
}
