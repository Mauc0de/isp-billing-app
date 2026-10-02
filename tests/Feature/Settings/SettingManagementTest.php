<?php

namespace Tests\Feature\Settings;

use App\Auth\TenantRoleProvisioner;
use App\Models\Tenant;
use App\Models\User;
use App\Settings\TenantSettings;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingManagementTest extends TestCase
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

    public function test_settings_page_renders_with_defaults(): void
    {
        $this->actingAs($this->admin)
            ->get('/pengaturan')
            ->assertOk()
            ->assertSee('Billing &amp; Suspensi', false)
            ->assertSee('Notifikasi WhatsApp')
            ->assertSee('Rekening Pembayaran')
            ->assertSee('Simpan Pengaturan');
    }

    public function test_admin_can_update_settings(): void
    {
        $this->actingAs($this->admin)
            ->post('/pengaturan', $this->payload([
                'grace_period_days' => 7,
                'reminder_days_before' => 5,
                'whatsapp_provider' => 'fonnte',
                'bank_name' => 'Mandiri',
                'bank_number' => '999888777',
            ]))
            ->assertRedirect();

        app(TenantContext::class)->run($this->tenant->getKey(), function (): void {
            $settings = app(TenantSettings::class);
            $settings->flush();

            $this->assertSame(7, $settings->gracePeriodDays());
            $this->assertSame(5, $settings->reminderDaysBefore());
            $this->assertSame('fonnte', $settings->whatsappProvider()->value);
            $this->assertSame('Mandiri', $settings->string(TenantSettings::BANK_NAME));
            $this->assertSame('999888777', $settings->string(TenantSettings::BANK_NUMBER));
        });
    }

    public function test_custom_template_is_used(): void
    {
        $this->actingAs($this->admin)
            ->post('/pengaturan', $this->payload([
                'template_suspend' => 'Halo {customer}, bayar {amount}.',
            ]))
            ->assertRedirect();

        app(TenantContext::class)->run($this->tenant->getKey(), function (): void {
            $settings = app(TenantSettings::class);
            $settings->flush();

            $this->assertSame('Halo {customer}, bayar {amount}.', $settings->template('suspend'));
        });
    }

    public function test_settings_do_not_leak_between_tenants(): void
    {
        $this->actingAs($this->admin)
            ->post('/pengaturan', $this->payload(['grace_period_days' => 14]))
            ->assertRedirect();

        $other = Tenant::factory()->create();

        app(TenantContext::class)->run($other->getKey(), function (): void {
            $settings = app(TenantSettings::class);
            $settings->flush();

            // Default 3, bukan 14 milik tenant pertama.
            $this->assertSame(3, $settings->gracePeriodDays());
        });
    }

    public function test_invalid_provider_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post('/pengaturan', $this->payload(['whatsapp_provider' => 'entah']))
            ->assertSessionHasErrors('whatsapp_provider');
    }

    public function test_user_without_permission_cannot_update(): void
    {
        $plain = app(TenantContext::class)->run($this->tenant->getKey(), fn (): User => User::factory()->create([
            'tenant_id' => $this->tenant->getKey(),
        ]));

        $this->actingAs($plain)
            ->post('/pengaturan', $this->payload())
            ->assertForbidden();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'grace_period_days' => 3,
            'reminder_days_before' => 3,
            'whatsapp_provider' => 'disabled',
            'template_suspend' => 'Template suspend',
            'template_reactivate' => 'Template reactivate',
            'template_due_reminder' => 'Template reminder',
            'bank_name' => 'BCA',
            'bank_number' => '1234567890',
            'bank_holder' => 'PT ISP',
        ], $overrides);
    }
}
