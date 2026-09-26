<?php

namespace Database\Seeders;

use App\Auth\TenantRoleProvisioner;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(PermissionSeeder::class);

        if (! app()->environment('local')) {
            return;
        }

        $tenant = Tenant::query()->updateOrCreate(
            ['slug' => 'demo-isp'],
            [
                'name' => 'Demo ISP',
                'email' => 'admin@demo.test',
                'timezone' => 'Asia/Jakarta',
                'is_active' => true,
            ],
        );

        $roles = app(TenantRoleProvisioner::class)->provision($tenant);
        $adminRole = $roles->firstWhere('slug', 'admin');

        if ($adminRole === null) {
            throw new \LogicException('The default admin role was not provisioned.');
        }

        app(TenantContext::class)->run($tenant->getKey(), function () use ($tenant, $adminRole): void {
            $user = User::query()->firstOrNew(['email' => 'admin@demo.test']);

            $user->fill([
                'tenant_id' => $tenant->getKey(),
                'name' => 'Demo Admin',
                'is_active' => true,
            ]);

            if (! $user->exists) {
                $user->password = Hash::make('password');
            }

            $user->save();
            $user->assignRole($adminRole);
        });
    }
}
