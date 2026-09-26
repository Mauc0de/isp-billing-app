<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        abort_unless(
            $user !== null && $user->is_active && $user->hasRole($role),
            403,
        );

        return $next($request);
    }
}
