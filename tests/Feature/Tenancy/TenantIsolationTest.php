<?php

namespace Tests\Feature\Tenancy;

use App\Models\Pelanggan;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use LogicException;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(TenantContext::class)->forget();
    }

    protected function tearDown(): void
    {
        app(TenantContext::class)->forget();

        parent::tearDown();
    }

    public function test_tenant_models_are_scoped_to_the_active_tenant(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $context = app(TenantContext::class);

        $context->run($tenantA->id, function (): void {
            $this->createPelanggan('PELANGGAN-A');
        });

        $context->run($tenantB->id, function (): void {
            $this->createPelanggan('PELANGGAN-B');
        });

        $visibleCustomers = $context->run($tenantA->id, fn (): array => Pelanggan::query()->get()->all());

        $this->assertCount(1, $visibleCustomers);
        $this->assertSame('PELANGGAN-A', $visibleCustomers[0]->nama);
    }

    public function test_tenant_queries_fail_closed_without_a_tenant_context(): void
    {
        $tenant = Tenant::factory()->create();

        app(TenantContext::class)->run($tenant->id, function (): void {
            $this->createPelanggan('PELANGGAN-A');
        });

        $this->assertSame(0, Pelanggan::query()->count());
    }

    public function test_tenant_models_cannot_be_created_with_a_mismatched_context(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $this->expectException(LogicException::class);

        app(TenantContext::class)->run(
            $tenantA->id,
            fn () => Pelanggan::query()->create([
                'tenant_id' => $tenantB->id,
                'nama' => 'Tenant B Pelanggan',
            ]),
        );
    }

    public function test_tenant_middleware_derives_tenant_from_authenticated_user(): void
    {
        Route::middleware(['web', 'auth', 'tenant'])
            ->get('/_test/tenant-context', function (Request $request): JsonResponse {
                return response()->json([
                    'context_tenant_id' => app(TenantContext::class)->id(),
                    'request_tenant_id' => $request->attributes->get('tenant_id'),
                ]);
            });

        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->getJson('/_test/tenant-context')
            ->assertOk()
            ->assertJson([
                'context_tenant_id' => $tenant->id,
                'request_tenant_id' => $tenant->id,
            ]);

        $this->assertNull(app(TenantContext::class)->id());
    }

    public function test_inactive_tenant_is_rejected_by_tenant_middleware(): void
    {
        Route::middleware(['web', 'auth', 'tenant'])
            ->get('/_test/inactive-tenant', fn (): string => 'unreachable');

        $tenant = Tenant::factory()->create(['is_active' => false]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get('/_test/inactive-tenant')
            ->assertForbidden();
    }

    private function createPelanggan(string $nama): Pelanggan
    {
        return Pelanggan::query()->create([
            'nama' => $nama,
        ]);
    }
}
