<?php

namespace Tests\Feature\Tenancy;

use App\Auth\TenantRoleProvisioner;
use App\Models\Pelanggan;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Isolasi tenant di level HTTP.
 *
 * Route memakai global scope BelongsToTenant melalui middleware `tenant`.
 * Test ini memastikan data tenant lain tidak bocor ke halaman staf.
 */
class TenantIsolationWebTest extends TestCase
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

    public function test_halaman_tidak_membocorkan_data_tenant_lain(): void
    {
        $tenantA = Tenant::factory()->create(['name' => 'ISP Alpha']);
        $tenantB = Tenant::factory()->create(['name' => 'ISP Beta']);

        app(TenantContext::class)->run($tenantA->getKey(), function (): void {
            Pelanggan::query()->create(['nama' => 'Pelanggan Milik Alpha']);
        });

        app(TenantContext::class)->run($tenantB->getKey(), function (): void {
            Pelanggan::query()->create(['nama' => 'Pelanggan Milik Beta']);
        });

        $userA = $this->adminOf($tenantA);

        $response = $this->actingAs($userA)->get('/pelanggan');

        $response->assertOk();
        $response->assertSee('Pelanggan Milik Alpha');
        $response->assertDontSee('Pelanggan Milik Beta');
    }

    public function test_dashboard_hanya_menghitung_pelanggan_tenant_sendiri(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        app(TenantContext::class)->run($tenantA->getKey(), function (): void {
            Pelanggan::query()->create(['nama' => 'Pelanggan Alpha 1']);
        });

        app(TenantContext::class)->run($tenantB->getKey(), function (): void {
            Pelanggan::query()->create(['nama' => 'Pelanggan Beta 1']);
            Pelanggan::query()->create(['nama' => 'Pelanggan Beta 2']);
        });

        $userA = $this->adminOf($tenantA);

        $response = $this->actingAs($userA)->get('/dashboard');

        $response->assertOk();
        $this->assertSame(1, $response->viewData('totalPelanggan'));
    }

    public function test_tamu_diarahkan_ke_halaman_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_user_nonaktif_ditolak_oleh_middleware_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'is_active' => false,
        ]);

        $this->actingAs($user)->get('/dashboard')->assertForbidden();
    }

    public function test_user_dari_tenant_nonaktif_ditolak(): void
    {
        $tenant = Tenant::factory()->create(['is_active' => false]);
        $user = User::factory()->create(['tenant_id' => $tenant->getKey()]);

        $this->actingAs($user)->get('/dashboard')->assertForbidden();
    }

    public function test_pelanggan_tanpa_role_tidak_boleh_melihat_area_staf(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->getKey()]);

        // Tanpa role/permission apa pun, halaman staf harus ditolak.
        $this->actingAs($user)->get('/dashboard')->assertForbidden();
    }

    /**
     * User dengan role admin (semua permission) pada tenant tertentu.
     */
    private function adminOf(Tenant $tenant): User
    {
        return app(TenantContext::class)->run($tenant->getKey(), function () use ($tenant): User {
            $admin = app(TenantRoleProvisioner::class)
                ->provision($tenant)
                ->firstWhere('slug', 'admin');

            $user = User::factory()->create(['tenant_id' => $tenant->getKey()]);
            $user->assignRole($admin);

            return $user;
        });
    }
}
