<?php

namespace Tests\Feature\Auth;

use App\Auth\TenantRoleProvisioner;
use App\Models\Pelanggan;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
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

    public function test_staff_is_redirected_to_dashboard_after_login(): void
    {
        $tenant = Tenant::factory()->create();

        $user = app(TenantContext::class)->run($tenant->getKey(), function () use ($tenant): User {
            $admin = app(TenantRoleProvisioner::class)
                ->provision($tenant)
                ->firstWhere('slug', 'admin');

            $user = User::factory()->create([
                'tenant_id' => $tenant->getKey(),
                'email' => 'staf@netisp.id',
                'password' => 'password123',
            ]);
            $user->assignRole($admin);

            return $user;
        });

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_customer_is_redirected_to_portal_after_login(): void
    {
        $tenant = Tenant::factory()->create();

        app(TenantContext::class)->run($tenant->getKey(), function () use ($tenant): void {
            $user = User::factory()->create([
                'tenant_id' => $tenant->getKey(),
                'email' => 'pelanggan@email.test',
                'password' => 'password123',
            ]);

            Pelanggan::query()->create([
                'nama' => 'Pelanggan Portal',
                'user_id' => $user->getKey(),
            ]);
        });

        $this->post('/login', [
            'email' => 'pelanggan@email.test',
            'password' => 'password123',
        ])->assertRedirect(route('portal.index'));
    }

    public function test_inactive_user_cannot_login(): void
    {
        $tenant = Tenant::factory()->create();

        app(TenantContext::class)->run($tenant->getKey(), function () use ($tenant): void {
            User::factory()->create([
                'tenant_id' => $tenant->getKey(),
                'email' => 'nonaktif@netisp.id',
                'password' => 'password123',
                'is_active' => false,
            ]);
        });

        $this->post('/login', [
            'email' => 'nonaktif@netisp.id',
            'password' => 'password123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_registration_creates_inactive_account_and_links_customer(): void
    {
        $tenant = Tenant::factory()->create();

        app(TenantContext::class)->run($tenant->getKey(), function (): void {
            Pelanggan::query()->create([
                'nama' => 'Calon Pelanggan',
                'email' => 'calon@email.test',
            ]);
        });

        $this->post('/register', [
            'name' => 'Calon Pelanggan',
            'email' => 'calon@email.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $user = app(TenantContext::class)->run(
            $tenant->getKey(),
            fn (): ?User => User::query()->where('email', 'calon@email.test')->first(),
        );

        $this->assertNotNull($user);
        $this->assertFalse((bool) $user->is_active, 'Akun hasil registrasi harus menunggu persetujuan.');

        $pelanggan = app(TenantContext::class)->run(
            $tenant->getKey(),
            fn (): ?Pelanggan => Pelanggan::query()->where('email', 'calon@email.test')->first(),
        );
        $this->assertSame($user->getKey(), $pelanggan->user_id);

        // Belum bisa login karena masih nonaktif.
        $this->assertGuest();
    }

    public function test_logout_requires_post_and_clears_session(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->getKey()]);

        Auth::login($user);
        $this->assertAuthenticated();

        $this->post('/logout')->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_logout_via_get_is_not_allowed(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->getKey()]);

        Auth::login($user);

        $this->get('/logout')->assertMethodNotAllowed();
        $this->assertAuthenticated();
    }
}
