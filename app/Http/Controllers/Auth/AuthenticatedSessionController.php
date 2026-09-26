<?php

namespace App\Http\Controllers\Auth;

use App\Auth\TenantRoleProvisioner;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Authenticate using the provided credentials.
     *
     * Aturan mengikuti kontrak tenancy di docs/backend-foundation.md:
     * user nonaktif dan tenant nonaktif tidak boleh memperoleh sesi.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('email', $credentials['email'])
            ->first();

        if ($user === null || ! Auth::validate($credentials)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => __('auth.inactive'),
            ]);
        }

        $tenant = $user->tenant;

        if ($tenant === null || ! $tenant->is_active) {
            throw ValidationException::withMessages([
                'email' => __('auth.inactive'),
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Register a new user into an existing tenant.
     *
     * Pendaftaran tidak membuat tenant baru. Tenant dibentuk oleh seeder atau
     * operasi administratif, dan user baru wajib punya tenant sebelum bisa login.
     * User baru diberi role `staff` sebagai akses awal.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'tenant_slug' => ['required', 'string', 'exists:tenants,slug'],
        ]);

        $tenant = Tenant::query()
            ->where('slug', $validated['tenant_slug'])
            ->where('is_active', true)
            ->first();

        if ($tenant === null) {
            throw ValidationException::withMessages([
                'tenant_slug' => __('auth.tenant_inactive'),
            ]);
        }

        $user = DB::transaction(function () use ($validated, $tenant): User {
            $created = app(TenantContext::class)->run(
                $tenant->getKey(),
                fn (): User => User::query()->create([
                    'tenant_id' => $tenant->getKey(),
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'is_active' => true,
                ]),
            );

            $staffRole = app(TenantRoleProvisioner::class)
                ->provision($tenant)
                ->firstWhere('slug', 'staff');

            $created->assignRole($staffRole);

            return $created;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    /**
     * Destroy an authenticated session.
     *
     * Tenant context dibersihkan eksplisit karena worker jangka panjang dapat
     * memakai instance TenantContext yang sama antar request.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        app(TenantContext::class)->forget();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
