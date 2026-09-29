<?php

namespace Database\Factories;

use App\Enums\RouterStatus;
use App\Enums\RouterSuspendMethod;
use App\Models\Router;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Router>
 */
class RouterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => 'Router '.fake()->unique()->city(),
            'location' => fake()->city(),
            'vpn_ip' => '10.'.fake()->numberBetween(0, 255).'.'.fake()->numberBetween(1, 254).'.1',
            'api_port' => 8728,
            'username' => 'satak-api',
            'password' => 'router-secret-'.fake()->lexify('??????'),
            'suspend_method' => RouterSuspendMethod::PppSecret,
            'use_ssl' => false,
            'status' => RouterStatus::Unknown,
            'is_active' => true,
        ];
    }

    /**
     * Router harus dibuat di dalam konteks tenant yang sama, jadi dipakai
     * tenant yang sudah ada, bukan membuat tenant baru.
     */
    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => [
            'tenant_id' => $tenant->getKey(),
        ]);
    }

    public function offline(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => RouterStatus::Offline,
            'last_error' => 'Connection refused',
        ]);
    }

    public function usingAddressList(string $listName = 'satak-blocklist'): static
    {
        return $this->state(fn (array $attributes): array => [
            'suspend_method' => RouterSuspendMethod::AddressList,
            'address_list_name' => $listName,
        ]);
    }
}
