<?php

namespace Tests\Feature\Saldo;

use App\Auth\TenantRoleProvisioner;
use App\Billing\AutoRenew;
use App\Billing\SaldoLedger;
use App\Enums\CustomerStatus;
use App\Enums\InvoiceStatus;
use App\Enums\SaldoMutationType;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\SaldoMutation;
use App\Models\Tagihan;
use App\Models\Tenant;
use App\Models\User;
use App\Suspension\OverdueScanRunner;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class SaldoManagementTest extends TestCase
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

    public function test_credit_increases_saldo_and_records_mutation(): void
    {
        $pelanggan = $this->makePelanggan();

        app(TenantContext::class)->run($this->tenant->getKey(), function () use ($pelanggan): void {
            app(SaldoLedger::class)->credit($pelanggan, 100000, 'manual');

            $this->assertSame(100000, $pelanggan->fresh()->saldo);

            $mutation = SaldoMutation::query()->sole();
            $this->assertSame(SaldoMutationType::Kredit, $mutation->jenis);
            $this->assertSame(100000, $mutation->jumlah);
            $this->assertSame(100000, $mutation->saldo_akhir);
        });
    }

    public function test_debit_below_zero_is_rejected(): void
    {
        $pelanggan = $this->makePelanggan();

        app(TenantContext::class)->run($this->tenant->getKey(), function () use ($pelanggan): void {
            app(SaldoLedger::class)->credit($pelanggan, 50000, 'manual');

            $this->expectException(LogicException::class);
            app(SaldoLedger::class)->debit($pelanggan, 80000, 'manual');
        });
    }

    public function test_auto_renew_pays_outstanding_invoice_from_saldo(): void
    {
        $pelanggan = $this->makePelanggan();
        $tagihan = null;

        app(TenantContext::class)->run($this->tenant->getKey(), function () use ($pelanggan, &$tagihan): void {
            app(SaldoLedger::class)->credit($pelanggan, 200000, 'manual');
            $tagihan = $this->makeTagihan($pelanggan, ['jumlah' => 150000]);

            $paid = app(AutoRenew::class)->pay($tagihan);

            $this->assertTrue($paid);

            $this->assertSame(50000, $pelanggan->fresh()->saldo);
            $this->assertSame(InvoiceStatus::Lunas->value, $tagihan->fresh()->status);
            $this->assertNotNull($tagihan->fresh()->tanggal_bayar);

            $payment = Pembayaran::query()->sole();
            $this->assertSame('saldo', $payment->metode_pembayaran);
            $this->assertSame(150000, $payment->jumlah);
        });
    }

    public function test_auto_renew_does_nothing_when_saldo_is_insufficient(): void
    {
        $pelanggan = $this->makePelanggan();

        app(TenantContext::class)->run($this->tenant->getKey(), function () use ($pelanggan): void {
            app(SaldoLedger::class)->credit($pelanggan, 10000, 'manual');
            $tagihan = $this->makeTagihan($pelanggan, ['jumlah' => 150000]);

            $paid = app(AutoRenew::class)->pay($tagihan);

            $this->assertFalse($paid);
            $this->assertSame(InvoiceStatus::BelumBayar->value, $tagihan->fresh()->status);
            $this->assertSame(10000, $pelanggan->fresh()->saldo);
            $this->assertSame(0, Pembayaran::query()->count());
        });
    }

    public function test_run_for_current_tenant_only_pays_auto_renew_customers(): void
    {
        app(TenantContext::class)->run($this->tenant->getKey(), function (): void {
            $auto = $this->makePelanggan(['auto_renew' => true, 'nama' => 'Auto']);
            $manual = $this->makePelanggan(['auto_renew' => false, 'nama' => 'Manual']);

            app(SaldoLedger::class)->credit($auto, 200000, 'manual');
            app(SaldoLedger::class)->credit($manual, 200000, 'manual');

            $this->makeTagihan($auto, ['jumlah' => 150000]);
            $this->makeTagihan($manual, ['jumlah' => 150000]);

            $paid = app(AutoRenew::class)->runForCurrentTenant();

            $this->assertSame(1, $paid);
            $this->assertSame(50000, $auto->fresh()->saldo);
            $this->assertSame(200000, $manual->fresh()->saldo);
        });
    }

    public function test_admin_can_top_up_via_http(): void
    {
        $pelanggan = $this->makePelanggan();

        $this->actingAs($this->admin)
            ->post("/saldo/{$pelanggan->getKey()}/topup", [
                'jumlah' => 75000,
                'keterangan' => 'Bayar tunai di kantor',
            ])
            ->assertRedirect();

        $this->assertSame(75000, $pelanggan->fresh()->saldo);
    }

    public function test_admin_can_toggle_auto_renew_via_http(): void
    {
        $pelanggan = $this->makePelanggan(['auto_renew' => false]);

        $this->actingAs($this->admin)
            ->patch("/saldo/{$pelanggan->getKey()}/auto-renew")
            ->assertRedirect();

        $this->assertTrue((bool) $pelanggan->fresh()->auto_renew);
    }

    public function test_admin_can_pay_invoice_from_saldo_via_http(): void
    {
        $pelanggan = $this->makePelanggan();
        $tagihan = null;

        app(TenantContext::class)->run($this->tenant->getKey(), function () use ($pelanggan, &$tagihan): void {
            app(SaldoLedger::class)->credit($pelanggan, 200000, 'manual');
            $tagihan = $this->makeTagihan($pelanggan, ['jumlah' => 150000]);
        });

        $this->actingAs($this->admin)
            ->post("/saldo/tagihan/{$tagihan->getKey()}/bayar")
            ->assertRedirect();

        $this->assertSame(InvoiceStatus::Lunas->value, $tagihan->fresh()->status);
        $this->assertSame(50000, $pelanggan->fresh()->saldo);
    }

    public function test_scan_runner_performs_auto_renew(): void
    {
        app(TenantContext::class)->run($this->tenant->getKey(), function (): void {
            $pelanggan = $this->makePelanggan(['auto_renew' => true]);
            app(SaldoLedger::class)->credit($pelanggan, 200000, 'manual');
            $this->makeTagihan($pelanggan, [
                'jumlah' => 150000,
                'jatuh_tempo' => now()->addDays(2)->toDateString(),
            ]);
        });

        $report = app(OverdueScanRunner::class)->run();

        $this->assertSame(1, $report->renewed);
        $this->assertSame(0, $report->suspended);
    }

    public function test_index_page_renders(): void
    {
        $this->makePelanggan();

        $this->actingAs($this->admin)
            ->get('/saldo')
            ->assertOk()
            ->assertSee('Saldo Pelanggan');
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $plain = app(TenantContext::class)->run($this->tenant->getKey(), function (): User {
            return User::factory()->create(['tenant_id' => $this->tenant->getKey()]);
        });

        $this->actingAs($plain)->get('/saldo')->assertForbidden();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function makePelanggan(array $attributes = []): Pelanggan
    {
        return app(TenantContext::class)->run($this->tenant->getKey(), fn (): Pelanggan => Pelanggan::query()->create(array_merge([
            'nama' => 'Pelanggan Saldo',
            'status' => CustomerStatus::Aktif->value,
        ], $attributes)));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function makeTagihan(Pelanggan $pelanggan, array $attributes = []): Tagihan
    {
        return Tagihan::query()->create(array_merge([
            'pelanggan_id' => $pelanggan->getKey(),
            'nomor_tagihan' => 'INV-'.strtoupper(bin2hex(random_bytes(3))),
            'jumlah' => 150000,
            'tanggal_terbit' => now()->toDateString(),
            'jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => InvoiceStatus::BelumBayar->value,
        ], $attributes));
    }
}
