<?php

namespace Tests\Unit\Whatsapp;

use App\Enums\WhatsappProvider;
use App\Models\Tenant;
use App\Settings\TenantSettings;
use App\Tenancy\TenantContext;
use App\Whatsapp\FonnteGateway;
use App\Whatsapp\NullGateway;
use App\Whatsapp\WablasGateway;
use App\Whatsapp\WhatsappGatewayFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\InteractsWithTenant;
use Tests\TestCase;

/**
 * Memastikan factory tidak pernah mengirim request yang pasti ditolak.
 *
 * Ini kondisi nyata sekarang: provider sudah dipilih per tenant, tapi token
 * dari kantor belum fillings. Membiarkan request terkirim hanya menghasilkan
 * notifikasi berstatus failed yang memenuhi antrean tanpa sebab jelas.
 */
class WhatsappGatewayFactoryTest extends TestCase
{
    use InteractsWithTenant;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'whatsapp.default_provider' => 'disabled',
            'whatsapp.fonnte.token' => null,
            'whatsapp.wablas.token' => null,
            'whatsapp.wablas.secret_key' => null,
        ]);
    }

    public function test_disabled_provider_always_uses_the_null_gateway(): void
    {
        $this->assertInstanceOf(NullGateway::class, $this->gatewayFor('disabled'));
    }

    public function test_fonnte_without_a_token_falls_back_to_the_null_gateway(): void
    {
        $this->assertInstanceOf(NullGateway::class, $this->gatewayFor('fonnte'));
    }

    public function test_fonnte_with_a_token_builds_the_real_gateway(): void
    {
        config(['whatsapp.fonnte.token' => 'TOKEN-ABC']);

        $this->assertInstanceOf(FonnteGateway::class, $this->gatewayFor('fonnte'));
    }

    public function test_wablas_without_a_token_falls_back_to_the_null_gateway(): void
    {
        $this->assertInstanceOf(NullGateway::class, $this->gatewayFor('wablas'));
    }

    /**
     * Authorization Wablas adalah "token.secret", jadi secret kosong sama
     * tidak adanya token.
     */
    public function test_wablas_with_a_token_but_no_secret_falls_back_to_the_null_gateway(): void
    {
        config(['whatsapp.wablas.token' => 'TOKEN-ABC']);

        $this->assertInstanceOf(NullGateway::class, $this->gatewayFor('wablas'));
    }

    public function test_wablas_with_both_credentials_builds_the_real_gateway(): void
    {
        config([
            'whatsapp.wablas.token' => 'TOKEN-ABC',
            'whatsapp.wablas.secret_key' => 'SECRET-XYZ',
        ]);

        $this->assertInstanceOf(WablasGateway::class, $this->gatewayFor('wablas'));
    }

    public function test_null_gateway_reports_skipped_without_touching_the_network(): void
    {
        Http::preventStrayRequests();

        $result = $this->gatewayFor('fonnte')->send('6281234567890', 'Halo');

        $this->assertTrue($result->skipped);
        $this->assertFalse($result->success);
        $this->assertStringContainsString('belum dikonfigurasi', (string) $result->error);
    }

    public function test_configured_provider_without_token_never_sends_a_request(): void
    {
        Http::preventStrayRequests();

        $this->gatewayFor('fonnte')->send('6281234567890', 'Halo');
        $this->gatewayFor('wablas')->send('6281234567890', 'Halo');

        Http::assertNothingSent();
    }

    public function test_provider_falls_back_to_the_config_default_when_the_tenant_has_none(): void
    {
        config(['whatsapp.default_provider' => WhatsappProvider::Fonnte->value]);

        $this->assertInstanceOf(NullGateway::class, $this->gatewayFor(null));
    }

    private function gatewayFor(?string $provider): object
    {
        // Idealnya tidak ada konteks tersisa dari test sebelumnya.
        app(TenantContext::class)->forget();

        $tenant = Tenant::factory()->create();

        return $this->inTenant($tenant, function () use ($provider): object {
            if ($provider !== null) {
                app(TenantSettings::class)->put(TenantSettings::WHATSAPP_PROVIDER, $provider);
            }

            return app(WhatsappGatewayFactory::class)->make();
        });
    }
}
