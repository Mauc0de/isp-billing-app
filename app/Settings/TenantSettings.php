<?php

namespace App\Settings;

use App\Enums\WhatsappProvider;
use App\Models\TenantSetting;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * Pembacaan konfigurasi per tenant dengan tipe yang sudah jelas.
 *
 * Hanya nilai non-rahasia yang dibaca dari tabel settings (provider, template
 * pesan, masa tenggang). Token API tidak ada di sini dan hanya dibaca dari
 * .env lewat config(), karena kolom settings berupa JSON plaintext sehingga
 * tidak aman dipakai menyimpan kredensial. Kalau suatu saat tiap tenant butuh
 * token sendiri, tambahkan kolom terenkripsi terpisah — bukan key baru di
 * tabel ini.
 */
class TenantSettings
{
    public const GRACE_PERIOD_DAYS = 'billing.suspension_grace_days';

    public const WHATSAPP_PROVIDER = 'whatsapp.provider';

    public const REMINDER_DAYS_BEFORE = 'billing.reminder_days_before';

    /**
     * @return array<string, mixed>|null
     */
    private ?array $values = null;

    /**
     * Tenant yang memuat cache $values. Wajib dicatat, karena satu instance
     * bisa melewati beberapa tenant dalam satu proses saat TenantRunner
     * mengiterasi tenant aktif.
     */
    private ?string $loadedFor = null;

    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly CacheRepository $cache,
    ) {}

    /**
     * Jumlah hari toleransi sebelum pelanggan otomatis disuspend setelah
     * tagihan lewat jatuh tempo.
     */
    public function gracePeriodDays(): int
    {
        return $this->integer(self::GRACE_PERIOD_DAYS, 3);
    }

    /**
     * Berapa hari sebelum jatuh tempo pengingat dikirim lewat WhatsApp.
     */
    public function reminderDaysBefore(): int
    {
        return $this->integer(self::REMINDER_DAYS_BEFORE, 3);
    }

    public function whatsappProvider(): WhatsappProvider
    {
        $raw = $this->string(self::WHATSAPP_PROVIDER);

        if ($raw === null) {
            return WhatsappProvider::from((string) config('whatsapp.default_provider'));
        }

        return WhatsappProvider::tryFrom($raw) ?? WhatsappProvider::Disabled;
    }

    /**
     * Template pesan, dengan fallback ke default di config/whatsapp.php.
     */
    public function template(string $key): string
    {
        $custom = $this->string('whatsapp.templates.'.$key);

        if ($custom !== null && $custom !== '') {
            return $custom;
        }

        $default = config('whatsapp.templates.'.$key);

        return is_string($default) ? $default : '';
    }

    public function put(string $key, mixed $value, string $type = 'string'): void
    {
        $tenantId = $this->tenantContext->id();

        if ($tenantId === null) {
            throw new \LogicException('A tenant context is required to write a tenant setting.');
        }

        TenantSetting::query()->updateOrCreate(
            ['tenant_id' => $tenantId, 'key' => $key],
            ['value' => $value, 'type' => $type],
        );

        $this->flush();
    }

    public function flush(): void
    {
        $this->values = null;
        $this->loadedFor = null;
        $this->cache->forget($this->cacheKey());
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $tenantId = $this->tenantContext->id();

        if ($this->values !== null && $this->loadedFor === $tenantId) {
            return $this->values;
        }

        $this->values = null;
        $this->loadedFor = $tenantId;

        if ($tenantId === null) {
            return $this->values = [];
        }

        $values = $this->cache->remember(
            $this->cacheKey(),
            now()->addMinutes(10),
            fn (): array => TenantSetting::query()
                ->pluck('value', 'key')
                ->all(),
        );

        return $this->values = $values;
    }

    public function string(string $key): ?string
    {
        $value = $this->all()[$key] ?? null;

        if ($value === null) {
            return null;
        }

        return is_scalar($value) ? trim((string) $value) : null;
    }

    public function integer(string $key, int $default): int
    {
        $value = $this->all()[$key] ?? null;

        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        return $default;
    }

    private function cacheKey(): string
    {
        return 'tenant-settings:'.($this->tenantContext->id() ?? 'none');
    }
}
