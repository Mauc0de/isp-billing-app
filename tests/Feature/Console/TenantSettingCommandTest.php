<?php

namespace Tests\Feature\Console;

use App\Models\Tenant;
use App\Settings\TenantSettings;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithTenant;
use Tests\TestCase;

class TenantSettingCommandTest extends TestCase
{
    use InteractsWithTenant;
    use RefreshDatabase;

    private Tenant $first;

    private Tenant $second;

    protected function setUp(): void
    {
        parent::setUp();

        $this->first = Tenant::factory()->create(['name' => 'ISP Pertama', 'slug' => 'isp-pertama']);
        $this->second = Tenant::factory()->create(['name' => 'ISP Kedua', 'slug' => 'isp-kedua']);
    }

    public function test_a_setting_written_without_tenant_flag_reaches_every_active_tenant(): void
    {
        $this->artisan('isp:setting billing.suspension_grace_days 7')
            ->assertSuccessful();

        $this->assertSame(7, $this->gracePeriodOf($this->first));
        $this->assertSame(7, $this->gracePeriodOf($this->second));
    }

    public function test_tenant_flag_limits_the_write_to_that_tenant(): void
    {
        // Opsi --tenant pernah ada di signature tapi tidak pernah dibaca, jadi
        // penulisan diam-diam bocor ke semua tenant.
        $this->artisan('isp:setting billing.suspension_grace_days 9 --tenant=isp-pertama')
            ->expectsOutputToContain('ISP Pertama')
            ->assertSuccessful();

        $this->assertSame(9, $this->gracePeriodOf($this->first));
        $this->assertSame(3, $this->gracePeriodOf($this->second), 'Tenant lain tidak boleh ikut berubah.');
    }

    public function test_tenant_flag_accepts_the_tenant_name(): void
    {
        $this->artisan('isp:setting billing.suspension_grace_days 4 --tenant="ISP Kedua"')
            ->assertSuccessful();

        $this->assertSame(4, $this->gracePeriodOf($this->second));
        $this->assertSame(3, $this->gracePeriodOf($this->first));
    }

    public function test_unknown_tenant_fails_loudly_instead_of_writing_everywhere(): void
    {
        $this->artisan('isp:setting billing.suspension_grace_days 9 --tenant=entah')
            ->expectsOutputToContain("Tenant 'entah' tidak ditemukan")
            ->assertFailed();

        $this->assertSame(3, $this->gracePeriodOf($this->first));
        $this->assertSame(3, $this->gracePeriodOf($this->second));
    }

    public function test_ambiguous_tenant_name_is_reported_rather_than_guessed(): void
    {
        // Dua tenant boleh memakai nama yang sama di data impor.
        $third = Tenant::factory()->create(['name' => 'ISP Pertama', 'slug' => 'isp-duplikat']);
        $this->artisan('isp:setting billing.suspension_grace_days 9 --tenant="ISP Pertama"')
            ->expectsOutputToContain('lebih dari satu tenant')
            ->assertFailed();

        $this->assertSame(3, $this->gracePeriodOf($this->first));
        $this->assertSame(3, $this->gracePeriodOf($third));
    }

    public function test_slug_disambiguates_an_ambiguous_name(): void
    {
        Tenant::factory()->create(['name' => 'ISP Pertama', 'slug' => 'isp-duplikat']);

        $this->artisan('isp:setting billing.suspension_grace_days 9 --tenant=isp-duplikat')
            ->assertSuccessful();

        $this->assertSame(3, $this->gracePeriodOf($this->first));
    }

    public function test_inactive_tenant_can_still_be_edited_by_name(): void
    {
        $this->first->forceFill(['is_active' => false])->save();

        $this->artisan('isp:setting billing.suspension_grace_days 11 --tenant=isp-pertama')
            ->assertSuccessful();

        $this->assertSame(11, $this->gracePeriodOf($this->first));
    }

    public function test_listing_settings_is_scoped_to_the_chosen_tenant(): void
    {
        $this->inTenant($this->first, fn () => app(TenantSettings::class)
            ->put(TenantSettings::GRACE_PERIOD_DAYS, 7, 'integer'));

        $this->artisan('isp:setting --tenant=isp-pertama')
            ->expectsOutputToContain(TenantSettings::GRACE_PERIOD_DAYS)
            ->assertSuccessful();

        // Tenant kedua belum punya setting apa pun.
        $this->artisan('isp:setting --tenant=isp-kedua')
            ->expectsOutputToContain('Belum ada setting untuk tenant ini')
            ->assertSuccessful();
    }

    public function test_reading_a_single_key_reports_when_it_is_unset(): void
    {
        $this->artisan('isp:setting whatsapp.provider')
            ->expectsOutputToContain('(tidak diset)')
            ->assertSuccessful();
    }

    private function gracePeriodOf(Tenant $tenant): int
    {
        app(TenantContext::class)->forget();

        return $this->inTenant(
            $tenant,
            fn (): int => app(TenantSettings::class)->gracePeriodDays(),
        );
    }
}
