<?php

namespace Tests\Feature\Vouchers;

use App\Auth\TenantRoleProvisioner;
use App\Enums\VoucherStatus;
use App\Models\Paket;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherBatch;
use App\Tenancy\TenantContext;
use App\Vouchers\VoucherGenerator;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherManagementTest extends TestCase
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

    public function test_admin_can_generate_a_batch_of_vouchers(): void
    {
        $response = $this->actingAs($this->admin)->post('/voucher', [
            'nama' => 'Batch Pagi',
            'jumlah' => 5,
            'masa_aktif_hari' => 30,
            'panjang_kode' => 8,
        ]);

        $response->assertRedirect();

        app(TenantContext::class)->run($this->tenant->getKey(), function (): void {
            $batch = VoucherBatch::query()->sole();
            $this->assertSame(5, $batch->jumlah);
            $this->assertSame(5, $batch->vouchers()->count());

            foreach ($batch->vouchers as $voucher) {
                $this->assertSame(8, strlen($voucher->kode));
                $this->assertSame(VoucherStatus::BelumDipakai, $voucher->status);
                // Tidak boleh mengandung karakter ambigu.
                $this->assertDoesNotMatchRegularExpression('/[01OIL]/', $voucher->kode);
            }
        });
    }

    public function test_generated_codes_are_unique(): void
    {
        app(TenantContext::class)->run($this->tenant->getKey(), function (): void {
            app(VoucherGenerator::class)->generate(
                nama: 'Batch Unik',
                paket: null,
                jumlah: 50,
                panjangKode: 8,
            );

            $total = Voucher::query()->count();
            $unique = Voucher::query()->distinct()->count('kode');

            $this->assertSame(50, $total);
            $this->assertSame($total, $unique);
        });
    }

    public function test_prefix_is_applied_to_codes(): void
    {
        app(TenantContext::class)->run($this->tenant->getKey(), function (): void {
            $batch = app(VoucherGenerator::class)->generate(
                nama: 'Batch Prefix',
                paket: null,
                jumlah: 3,
                prefix: 'WIFI-',
                panjangKode: 6,
            );

            foreach ($batch->vouchers as $voucher) {
                $this->assertStringStartsWith('WIFI-', $voucher->kode);
            }
        });
    }

    public function test_voucher_uses_package_price_snapshot(): void
    {
        app(TenantContext::class)->run($this->tenant->getKey(), function (): void {
            $paket = Paket::query()->create([
                'nama_paket' => 'Hotspot 1 Hari',
                'harga' => 5000,
                'status' => 'aktif',
            ]);

            $batch = app(VoucherGenerator::class)->generate(
                nama: 'Batch Hotspot',
                paket: $paket,
                jumlah: 2,
            );

            $this->assertSame(5000, $batch->vouchers->first()->harga);
        });
    }

    public function test_index_shows_vouchers(): void
    {
        app(TenantContext::class)->run($this->tenant->getKey(), function (): void {
            app(VoucherGenerator::class)->generate('Batch Tampil', null, 2);
        });

        $this->actingAs($this->admin)
            ->get('/voucher')
            ->assertOk()
            ->assertSee('Voucher Hotspot')
            ->assertSee('Batch Tampil');
    }

    public function test_batch_page_is_printable(): void
    {
        $batch = app(TenantContext::class)->run($this->tenant->getKey(), function (): VoucherBatch {
            return app(VoucherGenerator::class)->generate('Batch Cetak', null, 3);
        });

        $this->actingAs($this->admin)
            ->get("/voucher/batch/{$batch->getKey()}")
            ->assertOk()
            ->assertSee('Cetak');
    }

    public function test_only_unused_voucher_can_be_deleted(): void
    {
        $voucher = app(TenantContext::class)->run($this->tenant->getKey(), function (): Voucher {
            $batch = app(VoucherGenerator::class)->generate('Batch Hapus', null, 1);

            return $batch->vouchers->first();
        });

        $this->actingAs($this->admin)
            ->delete("/voucher/{$voucher->getKey()}")
            ->assertRedirect();

        app(TenantContext::class)->run($this->tenant->getKey(), function () use ($voucher): void {
            $this->assertNull(Voucher::query()->find($voucher->getKey()));
        });
    }

    public function test_used_voucher_cannot_be_deleted(): void
    {
        $voucher = app(TenantContext::class)->run($this->tenant->getKey(), function (): Voucher {
            $batch = app(VoucherGenerator::class)->generate('Batch Terpakai', null, 1);
            $voucher = $batch->vouchers->first();
            $voucher->forceFill([
                'status' => VoucherStatus::Terpakai,
                'dipakai_at' => now(),
            ])->save();

            return $voucher;
        });

        $this->actingAs($this->admin)
            ->delete("/voucher/{$voucher->getKey()}")
            ->assertForbidden();
    }

    public function test_user_without_permission_cannot_generate(): void
    {
        $plain = app(TenantContext::class)->run($this->tenant->getKey(), function (): User {
            return User::factory()->create(['tenant_id' => $this->tenant->getKey()]);
        });

        $this->actingAs($plain)->get('/voucher')->assertForbidden();
        $this->actingAs($plain)->post('/voucher', [
            'nama' => 'Nakal',
            'jumlah' => 1,
            'masa_aktif_hari' => 30,
            'panjang_kode' => 8,
        ])->assertForbidden();
    }

    public function test_vouchers_are_tenant_scoped(): void
    {
        $otherTenant = Tenant::factory()->create();

        app(TenantContext::class)->run($otherTenant->getKey(), function (): void {
            app(VoucherGenerator::class)->generate('Batch Tenant Lain', null, 2);
        });

        app(TenantContext::class)->run($this->tenant->getKey(), function (): void {
            $this->assertSame(0, Voucher::query()->count());
            $this->assertSame(0, VoucherBatch::query()->count());
        });
    }
}
