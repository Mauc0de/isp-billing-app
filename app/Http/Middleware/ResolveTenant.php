<?php

namespace App\Http\Middleware;

use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function __construct(private readonly TenantContext $tenantContext) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        abort_unless($user->is_active, 403);

        $tenant = $user->tenant;

        abort_unless($tenant !== null && $tenant->is_active, 403);

        $request->attributes->set('tenant_id', $tenant->getKey());

        return $this->tenantContext->run(
            $tenant->getKey(),
            fn (): Response => $next($request),
        );
    }
}
