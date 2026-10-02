<?php

namespace Tests\Feature;

use App\Auth\TenantRoleProvisioner;
use App\Enums\CustomerStatus;
use App\Models\Paket;
use App\Models\Pelanggan;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        app(TenantContext::class)->forget();
    }

    public function test_staff_pages_render(): void
    {
        $tenant = Tenant::factory()->create();

        $user = app(TenantContext::class)->run($tenant->getKey(), function () use ($tenant): User {
            $paket = Paket::query()->create([
                'nama_paket' => 'Home 10',
                'kecepatan' => '10 Mbps',
                'harga' => 150000,
                'status' => 'aktif',
            ]);
            Pelanggan::query()->create([
                'nama' => 'Budi',
                'status' => CustomerStatus::Aktif,
                'paket_id' => $paket->getKey(),
            ]);

            $admin = app(TenantRoleProvisioner::class)
                ->provision($tenant)
                ->firstWhere('slug', 'admin');

            $user = User::factory()->create(['tenant_id' => $tenant->getKey()]);
            $user->assignRole($admin);

            return $user;
        });

        foreach (['/dashboard', '/pelanggan', '/paket', '/tagihan', '/pembayaran', '/laporan', '/pengaturan'] as $url) {
            $response = $this->actingAs($user)->get($url);
            $this->assertSame(200, $response->status(), "Halaman {$url} gagal: ".$response->status());
        }
    }

    public function test_portal_pages_render_for_linked_customer(): void
    {
        $tenant = Tenant::factory()->create();

        $user = app(TenantContext::class)->run($tenant->getKey(), function () use ($tenant): User {
            $user = User::factory()->create(['tenant_id' => $tenant->getKey()]);

            Pelanggan::query()->create([
                'nama' => 'Siti',
                'status' => CustomerStatus::Aktif,
                'user_id' => $user->getKey(),
            ]);

            return $user;
        });

        foreach (['/portal', '/portal/tagihan', '/portal/pembayaran', '/portal/paket'] as $url) {
            $response = $this->actingAs($user)->get($url);
            $this->assertSame(200, $response->status(), "Halaman {$url} gagal: ".$response->status());
        }
    }

    public function test_portal_requires_authentication(): void
    {
        $this->get('/portal')->assertRedirect('/login');
    }
}
