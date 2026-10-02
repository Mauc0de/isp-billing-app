<?php

namespace Tests\Feature\Tenancy;

use App\Models\Pelanggan;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Isolasi tenant di level HTTP.
 *
 * Route memakai session('tenant_id') secara manual, sementara model memakai
 * global scope BelongsToTenant. Test ini memastikan keduanya tidak bocor:
 * data tenant lain tidak boleh muncul di halaman manapun.
 */
class TenantIsolationWebTest extends TestCase
{
    use RefreshDatabase;

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

        $userA = User::factory()->create(['tenant_id' => $tenantA->getKey()]);

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

        $userA = User::factory()->create(['tenant_id' => $tenantA->getKey()]);

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
}
