<?php

namespace Tests\Feature\Payments;

use App\Auth\TenantRoleProvisioner;
use App\Enums\CustomerStatus;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentRequestPurpose;
use App\Enums\PaymentRequestStatus;
use App\Models\PaymentRequest;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\Tenant;
use App\Models\User;
use App\Payments\ManualTransferGateway;
use App\Payments\PaymentRequestVerifier;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class PaymentRequestTest extends TestCase
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

    public function test_manual_gateway_creates_a_pending_request(): void
    {
        $pelanggan = $this->makePelanggan();

        app(TenantContext::class)->run($this->tenant->getKey(), function () use ($pelanggan): void {
            $request = app(ManualTransferGateway::class)->createRequest(
                pelanggan: $pelanggan,
                jumlah: 100000,
                tujuan: PaymentRequestPurpose::TopUpSaldo->value,
            );

            $this->assertSame(PaymentRequestStatus::Menunggu, $request->status);
            $this->assertSame('transfer_manual', $request->provider);
        });
    }

    public function test_approving_topup_credits_saldo(): void
    {
        $pelanggan = $this->makePelanggan();

        $request = app(TenantContext::class)->run($this->tenant->getKey(), function () use ($pelanggan): PaymentRequest {
            return app(ManualTransferGateway::class)->createRequest(
                pelanggan: $pelanggan,
                jumlah: 100000,
                tujuan: PaymentRequestPurpose::TopUpSaldo->value,
            );
        });

        app(TenantContext::class)->run($this->tenant->getKey(), function () use ($request): void {
            app(PaymentRequestVerifier::class)->approve($request, $this->admin);
        });

        $this->assertSame(100000, $pelanggan->fresh()->saldo);
        $this->assertSame(PaymentRequestStatus::Terverifikasi, $request->fresh()->status);
        $this->assertNotNull($request->fresh()->verified_at);
    }

    public function test_approving_invoice_payment_marks_invoice_paid(): void
    {
        $pelanggan = $this->makePelanggan();
        $tagihan = null;

        $request = app(TenantContext::class)->run($this->tenant->getKey(), function () use ($pelanggan, &$tagihan): PaymentRequest {
            $tagihan = Tagihan::query()->create([
                'pelanggan_id' => $pelanggan->getKey(),
                'nomor_tagihan' => 'INV-001',
                'jumlah' => 150000,
                'tanggal_terbit' => now()->toDateString(),
                'jatuh_tempo' => now()->addDays(7)->toDateString(),
                'status' => InvoiceStatus::BelumBayar->value,
            ]);

            return app(ManualTransferGateway::class)->createRequest(
                pelanggan: $pelanggan,
                jumlah: 150000,
                tujuan: PaymentRequestPurpose::BayarTagihan->value,
                tagihan: $tagihan,
            );
        });

        app(TenantContext::class)->run($this->tenant->getKey(), function () use ($request): void {
            app(PaymentRequestVerifier::class)->approve($request, $this->admin);
        });

        $this->assertSame(InvoiceStatus::Lunas->value, $tagihan->fresh()->status);

        app(TenantContext::class)->run($this->tenant->getKey(), function (): void {
            $this->assertSame(1, Pembayaran::query()->count());
            $this->assertSame('transfer_manual', Pembayaran::query()->sole()->metode_pembayaran);
        });

        // Top-up saldo tidak berubah.
        $this->assertSame(0, $pelanggan->fresh()->saldo);
    }

    public function test_verifying_twice_does_not_double_credit(): void
    {
        $pelanggan = $this->makePelanggan();

        $request = app(TenantContext::class)->run($this->tenant->getKey(), fn (): PaymentRequest => app(ManualTransferGateway::class)->createRequest(
            pelanggan: $pelanggan,
            jumlah: 50000,
            tujuan: PaymentRequestPurpose::TopUpSaldo->value,
        ));

        app(TenantContext::class)->run($this->tenant->getKey(), function () use ($request): void {
            $verifier = app(PaymentRequestVerifier::class);
            $verifier->approve($request, $this->admin);

            $this->expectException(LogicException::class);
            $verifier->approve($request->fresh(), $this->admin);
        });

        $this->assertSame(50000, $pelanggan->fresh()->saldo);
    }

    public function test_admin_can_approve_via_http(): void
    {
        $pelanggan = $this->makePelanggan();

        $request = app(TenantContext::class)->run($this->tenant->getKey(), fn (): PaymentRequest => app(ManualTransferGateway::class)->createRequest(
            pelanggan: $pelanggan,
            jumlah: 75000,
            tujuan: PaymentRequestPurpose::TopUpSaldo->value,
        ));

        $this->actingAs($this->admin)
            ->post("/pembayaran-masuk/{$request->getKey()}/setujui", ['catatan_admin' => 'OK'])
            ->assertRedirect();

        $this->assertSame(75000, $pelanggan->fresh()->saldo);
        $this->assertSame(PaymentRequestStatus::Terverifikasi, $request->fresh()->status);
    }

    public function test_admin_can_reject_via_http(): void
    {
        $pelanggan = $this->makePelanggan();

        $request = app(TenantContext::class)->run($this->tenant->getKey(), fn (): PaymentRequest => app(ManualTransferGateway::class)->createRequest(
            pelanggan: $pelanggan,
            jumlah: 75000,
            tujuan: PaymentRequestPurpose::TopUpSaldo->value,
        ));

        $this->actingAs($this->admin)
            ->post("/pembayaran-masuk/{$request->getKey()}/tolak", ['catatan_admin' => 'Bukti tidak jelas'])
            ->assertRedirect();

        $this->assertSame(PaymentRequestStatus::Ditolak, $request->fresh()->status);
        $this->assertSame(0, $pelanggan->fresh()->saldo);
    }

    public function test_index_page_renders(): void
    {
        $this->actingAs($this->admin)
            ->get('/pembayaran-masuk')
            ->assertOk()
            ->assertSee('Pembayaran Masuk');
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $plain = app(TenantContext::class)->run($this->tenant->getKey(), fn (): User => User::factory()->create([
            'tenant_id' => $this->tenant->getKey(),
        ]));

        $this->actingAs($plain)->get('/pembayaran-masuk')->assertForbidden();
    }

    public function test_requests_are_tenant_scoped(): void
    {
        $otherTenant = Tenant::factory()->create();

        app(TenantContext::class)->run($otherTenant->getKey(), function (): void {
            $pelanggan = Pelanggan::query()->create(['nama' => 'Lain', 'status' => CustomerStatus::Aktif->value]);
            app(ManualTransferGateway::class)->createRequest($pelanggan, 10000, PaymentRequestPurpose::TopUpSaldo->value);
        });

        app(TenantContext::class)->run($this->tenant->getKey(), function (): void {
            $this->assertSame(0, PaymentRequest::query()->count());
        });
    }

    private function makePelanggan(array $attributes = []): Pelanggan
    {
        return app(TenantContext::class)->run($this->tenant->getKey(), fn (): Pelanggan => Pelanggan::query()->create(array_merge([
            'nama' => 'Pelanggan Bayar',
            'status' => CustomerStatus::Aktif->value,
        ], $attributes)));
    }
}
