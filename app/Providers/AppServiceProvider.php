<?php

namespace App\Providers;

use App\Models\User;
use App\Settings\TenantSettings;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);

        // Scoped, bukan singleton: cache di dalam TenantSettings harus
        // dibuang di antara request, tapi tetap dipakai di dalam satu siklus
        // request/job. Perpindahan tenant di tengah satu proses ditangani
        // TenantRunner bersama CacheRepository.
        $this->app->scoped(TenantSettings::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability): ?bool {
            if (! $user->is_active) {
                return false;
            }

            return $user->hasPermissionTo($ability) ? true : null;
        });
    }
}
