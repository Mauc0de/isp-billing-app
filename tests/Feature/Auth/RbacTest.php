<?php

namespace Tests\Feature\Auth;

use App\Auth\PermissionCatalog;
use App\Auth\TenantRoleProvisioner;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use LogicException;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        app(TenantContext::class)->forget();
    }

    protected function tearDown(): void
    {
        app(TenantContext::class)->forget();

        parent::tearDown();
    }

    public function test_user_with_permission_can_access_protected_route(): void
    {
        Route::middleware(['web', 'auth', 'tenant', 'permission:customers.view'])
            ->get('/_test/permission-allowed', fn (): string => 'allowed');

        [$user] = $this->createUserWithPermission(PermissionCatalog::CUSTOMERS_VIEW);

        $this->actingAs($user)
            ->get('/_test/permission-allowed')
            ->assertOk()
            ->assertSee('allowed');
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        Route::middleware(['web', 'auth', 'tenant', 'permission:customers.view'])
            ->get('/_test/permission-denied', fn (): string => 'denied');

        [$user] = $this->createUserWithPermission(PermissionCatalog::PACKAGES_VIEW);

        $this->actingAs($user)
            ->get('/_test/permission-denied')
            ->assertForbidden();
    }

    public function test_permission_from_another_tenant_is_not_visible(): void
    {
        [$user] = $this->createUserWithPermission(PermissionCatalog::CUSTOMERS_VIEW);
        $otherTenant = Tenant::factory()->create();

        $hasPermission = app(TenantContext::class)->run(
            $otherTenant->getKey(),
            fn (): bool => $user->hasPermissionTo(PermissionCatalog::CUSTOMERS_VIEW),
        );

        $this->assertFalse($hasPermission);
    }

    public function test_role_from_another_tenant_cannot_be_assigned(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenantA->getKey()]);

        $otherTenantRole = app(TenantContext::class)->run(
            $tenantB->getKey(),
            fn (): Role => Role::query()->create([
                'name' => 'Other Tenant Admin',
                'slug' => 'admin',
            ]),
        );

        $this->expectException(LogicException::class);

        app(TenantContext::class)->run(
            $tenantA->getKey(),
            fn () => $user->assignRole($otherTenantRole),
        );
    }

    public function test_default_roles_receive_expected_permissions(): void
    {
        $tenant = Tenant::factory()->create();
        $roles = app(TenantRoleProvisioner::class)->provision($tenant);

        $this->assertCount(4, $roles);
        $this->assertEqualsCanonicalizing(
            ['admin', 'finance', 'staff', 'technician'],
            $roles->pluck('slug')->all(),
        );

        app(TenantContext::class)->run($tenant->getKey(), function () use ($roles): void {
            $admin = $roles->firstWhere('slug', 'admin');
            $finance = $roles->firstWhere('slug', 'finance');

            $this->assertSame(
                Permission::query()->count(),
                $admin->permissions()->count(),
            );
            $this->assertTrue($finance->permissions()->where('slug', PermissionCatalog::PAYMENTS_CREATE)->exists());
            $this->assertFalse($finance->permissions()->where('slug', PermissionCatalog::USERS_DELETE)->exists());
        });
    }

    /**
     * @return array{0: User}
     */
    private function createUserWithPermission(string $permissionSlug): array
    {
        $tenant = Tenant::factory()->create();
        $permission = Permission::query()->where('slug', $permissionSlug)->firstOrFail();

        return app(TenantContext::class)->run($tenant->getKey(), function () use ($tenant, $permission): array {
            $role = Role::query()->create([
                'name' => 'Test Role',
                'slug' => 'test-role',
            ]);
            $role->permissions()->attach([
                $permission->getKey() => ['tenant_id' => $tenant->getKey()],
            ]);

            $user = User::factory()->create(['tenant_id' => $tenant->getKey()]);
            $user->assignRole($role);

            return [$user];
        });
    }
}
