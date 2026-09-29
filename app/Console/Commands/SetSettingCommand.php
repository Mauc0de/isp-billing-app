<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Settings\TenantSettings;
use App\Tenancy\TenantRunner;
use Illuminate\Console\Command;

/**
 * Mengatur konfigurasi tenant sementara UI Pengaturan belum tersedia.
 *
 * Nilai ditulis lewat TenantSettings::put() supaya ikut ter-cache dan
 * tervalidasi tipenya, sama seperti kalau ditulis dari UI.
 */
class SetSettingCommand extends Command
{
    protected $signature = 'isp:setting
        {key? : Kunci setting. Kosongkan untuk menampilkan semua setting tenant.}
        {value? : Nilai baru. Kosongkan untuk menghapus setting tersebut.}
        {--tenant= : Nama atau slug tenant. Kosongkan untuk semua tenant aktif.}
        {--json : Nilai dibaca sebagai JSON (untuk bool, number, array, object)}';

    protected $description = 'Tampilkan atau ubah konfigurasi per tenant (masa tenggang, provider WhatsApp, template pesan)';

    public function handle(TenantRunner $tenants, TenantSettings $settings): int
    {
        $target = $this->option('tenant');

        if ($target === null) {
            $tenants->forEachActiveTenant(fn (): mixed => $this->applyToCurrentTenant($settings));

            return self::SUCCESS;
        }

        return $this->applyToOneTenant($tenants, $settings, (string) $target);
    }

    private function applyToOneTenant(TenantRunner $tenants, TenantSettings $settings, string $target): int
    {
        // Ambil paling banyak dua supaya nama yang ambigu bisa dilaporkan,
        // bukan diam-diam memakai tenant pertama.
        $matches = Tenant::query()
            ->where('name', $target)
            ->orWhere('slug', $target)
            ->limit(2)
            ->get();

        if ($matches->isEmpty()) {
            $this->error("Tenant '{$target}' tidak ditemukan.");

            return self::FAILURE;
        }

        if ($matches->count() > 1) {
            $this->error("'{$target}' cocok dengan lebih dari satu tenant. Pakai slug yang lebih spesifik.");
            $this->line('  - '.$matches->pluck('slug')->implode(', '));

            return self::FAILURE;
        }

        $tenant = $matches->first();

        $tenants->forTenant($tenant, function () use ($settings, $tenant): void {
            $this->comment("Tenant: {$tenant->name} ({$tenant->slug})");
            $this->applyToCurrentTenant($settings);
        });

        return self::SUCCESS;
    }

    private function applyToCurrentTenant(TenantSettings $settings): void
    {
        $key = $this->argument('key');

        if ($key === null) {
            $this->showAll($settings);

            return;
        }

        $value = $this->argument('value');

        if ($value === null) {
            $this->line($key.' = '.$this->format($settings->all()[$key] ?? null));

            return;
        }

        $settings->put($key, $this->parseValue($value), $this->guessType($value));

        $this->info("{$key} disimpan.");
    }

    private function showAll(TenantSettings $settings): void
    {
        $values = $settings->all();

        if ($values === []) {
            $this->comment('Belum ada setting untuk tenant ini.');

            return;
        }

        foreach ($values as $key => $value) {
            $this->line(str_pad($key, 40).' = '.$this->format($value));
        }
    }

    private function format(mixed $value): string
    {
        return $value === null ? '(tidak diset)' : json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    private function parseValue(string $value): mixed
    {
        if ($this->option('json')) {
            return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        }

        return $value;
    }

    private function guessType(string $value): string
    {
        if (! $this->option('json')) {
            return 'string';
        }

        return match (true) {
            is_bool(json_decode($value)) => 'boolean',
            is_int(json_decode($value)) => 'integer',
            is_float(json_decode($value)) => 'float',
            default => 'string',
        };
    }
}
