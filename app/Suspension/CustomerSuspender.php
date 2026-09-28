<?php

namespace App\Suspension;

use App\Enums\CustomerStatus;
use App\Enums\RouterSuspendMethod;
use App\Enums\SuspendAction;
use App\Enums\SuspendLogStatus;
use App\Enums\SuspensionSource;
use App\Exceptions\RouterOperationFailed;
use App\Mikrotik\RouterClientFactory;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Router;
use App\Models\SuspendLog;
use App\Models\User;
use App\Whatsapp\WhatsappNotifier;
use Illuminate\Support\Facades\DB;

/**
 * Pusat logika suspend dan reaktivasi pelanggan.
 *
 * Aturan yang dipegang kelas ini:
 *  1. Setiap percobaan selalu menghasilkan satu baris suspend_logs, sukses
 *     maupun gagal, karena investigasi suspend salah selalu butuh bukti.
 *  2. Status pelanggan di database HANYA berubah kalau operasi di router
 *     benar-benar berhasil. Kalau router mati, tagihan tetap overdue sehingga
 *     percobaan otomatis diulang pada pemindaian berikutnya.
 *  3. Notifikasi WhatsApp hanya dikirim setelah operasi router sukses.
 */
class CustomerSuspender
{
    public function __construct(
        private readonly RouterClientFactory $routers,
        private readonly WhatsappNotifier $notifier,
    ) {}

    public function suspend(
        Customer $customer,
        SuspensionSource $source,
        ?Invoice $invoice = null,
        ?string $reason = null,
        ?User $actor = null,
    ): SuspendLog {
        if ($customer->status === CustomerStatus::Suspended) {
            return $this->skip(
                $customer,
                SuspendAction::Suspend,
                $source,
                'Pelanggan sudah dalam status suspended.',
                $invoice,
                $actor,
            );
        }

        $router = $customer->router;
        $username = $customer->mikrotik_username;

        if ($router === null || $username === null || $username === '') {
            return $this->skip(
                $customer,
                SuspendAction::Suspend,
                $source,
                'Pelanggan belum ditautkan ke router atau belum punya username Mikrotik.',
                $invoice,
                $actor,
            );
        }

        $method = $this->perform($router, $username, $router->suspend_method, SuspendAction::Suspend);

        if ($method['error'] !== null) {
            return $this->fail(
                customer: $customer,
                action: SuspendAction::Suspend,
                source: $source,
                method: $method['method'],
                reason: $reason,
                error: $method['error']->getMessage(),
                router: $router,
                invoice: $invoice,
                actor: $actor,
            );
        }

        $customer->forceFill([
            'status' => CustomerStatus::Suspended,
            'suspension_source' => $source,
            'status_reason' => $reason,
            'suspended_at' => now(),
            'suspended_by_id' => $actor?->getKey(),
        ])->save();

        $log = SuspendLog::query()->create([
            'tenant_id' => $customer->tenant_id,
            'customer_id' => $customer->getKey(),
            'router_id' => $router->getKey(),
            'invoice_id' => $invoice?->getKey(),
            'performed_by_id' => $actor?->getKey(),
            'action' => SuspendAction::Suspend,
            'source' => $source,
            'method' => $method['method'],
            'status' => SuspendLogStatus::Succeeded,
            'reason' => $reason,
            'context' => $this->context($method, $router),
            'performed_at' => now(),
        ]);

        $this->notifier->notifySuspended($log, $customer, $invoice);

        return $log;
    }

    public function reactivate(
        Customer $customer,
        ?string $reason = null,
        ?User $actor = null,
    ): SuspendLog {
        $source = $customer->suspension_source ?? SuspensionSource::Manual;

        if ($customer->status !== CustomerStatus::Suspended) {
            return $this->skip(
                $customer,
                SuspendAction::Reactivate,
                $source,
                'Pelanggan tidak dalam status suspended.',
                null,
                $actor,
            );
        }

        $router = $customer->router;
        $username = $customer->mikrotik_username;

        if ($router === null || $username === null || $username === '') {
            return $this->skip(
                $customer,
                SuspendAction::Reactivate,
                $source,
                'Pelanggan belum ditautkan ke router atau belum punya username Mikrotik.',
                null,
                $actor,
            );
        }

        $method = $this->perform($router, $username, $router->suspend_method, SuspendAction::Reactivate);

        if ($method['error'] !== null) {
            return $this->fail(
                customer: $customer,
                action: SuspendAction::Reactivate,
                source: $source,
                method: $method['method'],
                reason: $reason,
                error: $method['error']->getMessage(),
                router: $router,
                invoice: null,
                actor: $actor,
            );
        }

        $customer->forceFill([
            'status' => CustomerStatus::Active,
            'suspension_source' => null,
            'status_reason' => null,
            'suspended_at' => null,
            'suspended_by_id' => null,
        ])->save();

        $log = SuspendLog::query()->create([
            'tenant_id' => $customer->tenant_id,
            'customer_id' => $customer->getKey(),
            'router_id' => $router->getKey(),
            'performed_by_id' => $actor?->getKey(),
            'action' => SuspendAction::Reactivate,
            'source' => $source,
            'method' => $method['method'],
            'status' => SuspendLogStatus::Succeeded,
            'reason' => $reason,
            'context' => $this->context($method, $router),
            'performed_at' => now(),
        ]);

        $this->notifier->notifyReactivated($log, $customer);

        return $log;
    }

    /**
     * Jalankan operasi ke router, dengan fallback dari address-list ke PPP secret.
     *
     * Kalau router disuruh memakai address-list tapi pelanggan tidak punya IP
     * yang bisa diblokir (mis. sedang offline, jadi remote-address kosong),
     * dicoba lagi memakai disable PPP secret. Menolak suspend hanya karena
     * metode yang dikonfigurasi tidak cocok akan jauh lebih buruk daripada
     * memakai metode yang jelas berhasil.
     *
     * @return array{method: RouterSuspendMethod, error: RouterOperationFailed|null, fallback: bool}
     */
    private function perform(
        Router $router,
        string $username,
        RouterSuspendMethod $configured,
        SuspendAction $action,
    ): array {
        try {
            match ($action) {
                SuspendAction::Suspend => $this->routers->make($router)->suspend($configured, $username),
                SuspendAction::Reactivate => $this->routers->make($router)->reactivate($configured, $username),
            };

            return ['method' => $configured, 'error' => null, 'fallback' => false];
        } catch (RouterOperationFailed $exception) {
            $canFallback = $exception->addressUnavailable
                && $configured === RouterSuspendMethod::AddressList
                && $action === SuspendAction::Suspend;

            if (! $canFallback) {
                return ['method' => $configured, 'error' => $exception, 'fallback' => false];
            }

            try {
                $this->routers->make($router)->suspend(RouterSuspendMethod::PppSecret, $username);

                return [
                    'method' => RouterSuspendMethod::PppSecret,
                    'error' => null,
                    'fallback' => true,
                ];
            } catch (RouterOperationFailed $fallbackException) {
                return ['method' => $configured, 'error' => $fallbackException, 'fallback' => true];
            }
        }
    }

    /**
     * Catat di audit trail kalau metode yang dipakai berbeda dari konfigurasi
     * router, supaya kejadian ini bisa ditelusuri di kemudian hari.
     *
     * @param  array{method: RouterSuspendMethod, error: RouterOperationFailed|null, fallback: bool}  $result
     * @return array<string, string>|null
     */
    private function context(array $result, Router $router): ?array
    {
        if (! $result['fallback']) {
            return null;
        }

        return [
            'configured_method' => $router->suspend_method->value,
            'applied_method' => $result['method']->value,
            'note' => 'Fallback ke disable PPP secret karena address-list tidak bisa dipakai.',
        ];
    }

    private function skip(
        Customer $customer,
        SuspendAction $action,
        SuspensionSource $source,
        string $reason,
        ?Invoice $invoice,
        ?User $actor,
    ): SuspendLog {
        return SuspendLog::query()->create([
            'tenant_id' => $customer->tenant_id,
            'customer_id' => $customer->getKey(),
            'invoice_id' => $invoice?->getKey(),
            'performed_by_id' => $actor?->getKey(),
            'action' => $action,
            'source' => $source,
            'status' => SuspendLogStatus::Skipped,
            'reason' => $reason,
            'performed_at' => now(),
        ]);
    }

    private function fail(
        Customer $customer,
        SuspendAction $action,
        SuspensionSource $source,
        RouterSuspendMethod $method,
        ?string $reason,
        string $error,
        Router $router,
        ?Invoice $invoice,
        ?User $actor,
    ): SuspendLog {
        return DB::transaction(fn (): SuspendLog => SuspendLog::query()->create([
            'tenant_id' => $customer->tenant_id,
            'customer_id' => $customer->getKey(),
            'router_id' => $router->getKey(),
            'invoice_id' => $invoice?->getKey(),
            'performed_by_id' => $actor?->getKey(),
            'action' => $action,
            'source' => $source,
            'method' => $method,
            'status' => SuspendLogStatus::Failed,
            'reason' => $reason,
            'error' => $error,
            'performed_at' => now(),
        ]));
    }
}
