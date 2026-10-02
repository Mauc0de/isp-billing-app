<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Satu pintu masuk untuk admin maupun pelanggan.
     *
     * Setelah kredensial valid, tujuan diarahkan otomatis: user yang tertaut
     * ke data pelanggan masuk ke portal, selebihnya (staff/admin) ke dashboard.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if ($user === null || ! Auth::validate($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Akun Anda belum aktif. Hubungi administrator.',
            ]);
        }

        $tenant = $user->tenant;

        if ($tenant === null || ! $tenant->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Tenant tidak aktif. Hubungi administrator.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        return redirect()->intended($this->homeFor($user));
    }

    /**
     * Pendaftaran akun mandiri (request access).
     *
     * Akun dibuat NONAKTIF dan baru bisa dipakai setelah administrator
     * mengaktifkannya. Bila sudah ada data pelanggan dengan email yang sama
     * di tenant tersebut, akun langsung ditautkan supaya portal pelanggan
     * mengenali pemiliknya.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'tenant_slug' => ['nullable', 'string', 'exists:tenants,slug'],
        ]);

        $tenant = $this->resolveRegistrationTenant($validated['tenant_slug'] ?? null);

        if ($tenant === null) {
            throw ValidationException::withMessages([
                'email' => 'Belum ada tenant aktif. Hubungi administrator.',
            ]);
        }

        app(TenantContext::class)->run($tenant->getKey(), function () use ($validated, $tenant): void {
            DB::transaction(function () use ($validated, $tenant): void {
                $user = User::query()->create([
                    'tenant_id' => $tenant->getKey(),
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'is_active' => false,
                ]);

                // Tautkan ke data pelanggan yang emailnya cocok dan belum
                // punya akun, supaya portal langsung mengenali pemiliknya.
                Pelanggan::query()
                    ->whereNull('user_id')
                    ->where('email', $validated['email'])
                    ->update(['user_id' => $user->getKey()]);

                // Sengaja tidak diberi role apa pun: akun menunggu persetujuan,
                // dan administrator yang menentukan role-nya saat mengaktifkan.
            });
        });

        return redirect()
            ->route('login')
            ->with('status', 'Pendaftaran berhasil. Akun Anda menunggu persetujuan administrator.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        app(TenantContext::class)->forget();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Halaman tujuan setelah login: portal untuk pelanggan, dashboard untuk staf.
     *
     * Dicek tanpa global scope tenant karena saat login konteks tenant belum
     * diturunkan (itu tugas middleware `tenant` setelah request masuk).
     */
    private function homeFor(User $user): string
    {
        $isPelanggan = Pelanggan::withoutGlobalScopes()
            ->where('user_id', $user->getKey())
            ->exists();

        return $isPelanggan ? route('portal.index') : route('dashboard');
    }

    /**
     * Tenant untuk pendaftaran: pakai slug bila diberi, kalau tidak tenant
     * aktif pertama. Aplikasi ini berjalan satu tenant per instalasi pilot.
     */
    private function resolveRegistrationTenant(?string $slug): ?Tenant
    {
        return Tenant::query()
            ->where('is_active', true)
            ->when($slug !== null, fn ($query) => $query->where('slug', $slug))
            ->orderBy('created_at')
            ->first();
    }
}
