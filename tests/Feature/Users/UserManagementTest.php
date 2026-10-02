<?php

namespace Tests\Feature\Users;

use App\Auth\PermissionCatalog;
use App\Auth\TenantRoleProvisioner;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        app(TenantContext::class)->forget();

        $this->tenant = Tenant::factory()->create();

        $this->admin = app(TenantContext::class)->run($this->tenant->getKey(), function (): User {
            $adminRole = app(TenantRoleProvisioner::class)
                ->provision($this->tenant)
                ->firstWhere('slug', 'admin');

            $user = User::factory()->create(['tenant_id' => $this->tenant->getKey()]);
            $user->assignRole($adminRole);

            return $user;
        });
    }

    protected function tearDown(): void
    {
        app(TenantContext::class)->forget();

        parent::tearDown();
    }

    public function test_index_lists_users_of_the_tenant(): void
    {
        app(TenantContext::class)->run($this->tenant->getKey(), function (): void {
            User::factory()->create([
                'tenant_id' => $this->tenant->getKey(),
                'name' => 'Staf Baru',
            ]);
        });

        $this->actingAs($this->admin)
            ->get('/pengguna')
            ->assertOk()
            ->assertSee('Staf Baru')
            ->assertSee($this->admin->email)
            ->assertSee('Tambah Pengguna Baru');
    }

    public function test_user_menu_link_is_visible_only_to_authorized_users(): void
    {
        $this->actingAs($this->admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('href="/pengguna"', false);

        // Role staff bisa membuka dashboard tapi tidak punya users.view,
        // jadi tautan menu tidak boleh dirender.
        $staff = app(TenantContext::class)->run($this->tenant->getKey(), function (): User {
            $staffRole = app(TenantRoleProvisioner::class)
                ->provision($this->tenant)
                ->firstWhere('slug', 'staff');

            $user = User::factory()->create(['tenant_id' => $this->tenant->getKey()]);
            $user->assignRole($staffRole);

            return $user;
        });

        $this->actingAs($staff)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('href="/pengguna"', false);
    }

    public function test_admin_can_create_user_with_role(): void
    {
        $response = $this->actingAs($this->admin)->post('/pengguna', [
            'name' => 'Teknisi Lapangan',
            'email' => 'teknisi@netisp.id',
            'password' => 'rahasia123',
            'role' => 'technician',
        ]);

        $response->assertRedirect();

        $created = app(TenantContext::class)->run(
            $this->tenant->getKey(),
            fn (): ?User => User::query()->where('email', 'teknisi@netisp.id')->first(),
        );

        $this->assertNotNull($created);
        $this->assertTrue((bool) $created->is_active);
        $this->assertTrue(Hash::check('rahasia123', $created->password));
        $this->assertSame($this->tenant->getKey(), $created->tenant_id);

        app(TenantContext::class)->run($this->tenant->getKey(), function () use ($created): void {
            $this->assertTrue($created->hasRole('technician'));
        });
    }

    public function test_admin_can_change_a_users_role(): void
    {
        $target = app(TenantContext::class)->run($this->tenant->getKey(), function (): User {
            return User::factory()->create(['tenant_id' => $this->tenant->getKey()]);
        });

        $this->actingAs($this->admin)
            ->patch("/pengguna/{$target->getKey()}/role", ['role' => 'finance'])
            ->assertRedirect();

        $fresh = app(TenantContext::class)->run(
            $this->tenant->getKey(),
            fn (): User => $target->fresh(),
        );

        app(TenantContext::class)->run($this->tenant->getKey(), function () use ($fresh): void {
            $this->assertTrue($fresh->hasRole('finance'));
        });
    }

    public function test_admin_can_toggle_active_status(): void
    {
        $target = app(TenantContext::class)->run($this->tenant->getKey(), function (): User {
            return User::factory()->create([
                'tenant_id' => $this->tenant->getKey(),
                'is_active' => true,
            ]);
        });

        $this->actingAs($this->admin)
            ->patch("/pengguna/{$target->getKey()}/status")
            ->assertRedirect();

        $this->assertFalse((bool) $target->fresh()->is_active);

        // Menekan sekali lagi mengaktifkan kembali.
        $this->actingAs($this->admin)
            ->patch("/pengguna/{$target->getKey()}/status")
            ->assertRedirect();

        $this->assertTrue((bool) $target->fresh()->is_active);
    }

    public function test_admin_cannot_deactivate_themselves(): void
    {
        $this->actingAs($this->admin)
            ->patch("/pengguna/{$this->admin->getKey()}/status")
            ->assertForbidden();

        $this->assertTrue((bool) $this->admin->fresh()->is_active);
    }

    public function test_admin_can_reset_password(): void
    {
        $target = app(TenantContext::class)->run($this->tenant->getKey(), function (): User {
            return User::factory()->create([
                'tenant_id' => $this->tenant->getKey(),
                'password' => 'password-lama',
            ]);
        });

        $this->actingAs($this->admin)
            ->patch("/pengguna/{$target->getKey()}/password", [
                'password' => 'password-baru',
                'password_confirmation' => 'password-baru',
            ])
            ->assertRedirect();

        $this->assertTrue(Hash::check('password-baru', $target->fresh()->password));
    }

    public function test_user_without_permission_cannot_access(): void
    {
        $plain = app(TenantContext::class)->run($this->tenant->getKey(), function (): User {
            return User::factory()->create(['tenant_id' => $this->tenant->getKey()]);
        });

        $this->actingAs($plain)->get('/pengguna')->assertForbidden();
        $this->actingAs($plain)->post('/pengguna', [
            'name' => 'X',
            'email' => 'x@netisp.id',
            'password' => 'rahasia123',
        ])->assertForbidden();
    }

    public function test_admin_cannot_manage_users_from_another_tenant(): void
    {
        $otherTenant = Tenant::factory()->create();

        $outsider = app(TenantContext::class)->run($otherTenant->getKey(), function () use ($otherTenant): User {
            return User::factory()->create(['tenant_id' => $otherTenant->getKey()]);
        });

        // Ditolak seolah tidak ada, jadi tidak bocor keberadaannya.
        $this->actingAs($this->admin)
            ->patch("/pengguna/{$outsider->getKey()}/status")
            ->assertNotFound();
    }

    public function test_create_requires_unique_email(): void
    {
        app(TenantContext::class)->run($this->tenant->getKey(), function (): void {
            User::factory()->create([
                'tenant_id' => $this->tenant->getKey(),
                'email' => 'duplikat@netisp.id',
            ]);
        });

        $this->actingAs($this->admin)
            ->post('/pengguna', [
                'name' => 'Duplikat',
                'email' => 'duplikat@netisp.id',
                'password' => 'rahasia123',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_admin_role_has_user_management_permissions(): void
    {
        app(TenantContext::class)->run($this->tenant->getKey(), function (): void {
            $this->assertTrue($this->admin->hasPermissionTo(PermissionCatalog::USERS_VIEW));
            $this->assertTrue($this->admin->hasPermissionTo(PermissionCatalog::USERS_CREATE));
            $this->assertTrue($this->admin->hasPermissionTo(PermissionCatalog::USERS_UPDATE));
        });
    }
}
