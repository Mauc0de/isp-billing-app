<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use App\Enums\SuspensionSource;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelanggan extends Model
{
    use BelongsToTenant;
    use HasFactory, HasUlids;

    protected $table = 'pelanggans';

    protected $fillable = [
        'tenant_id',
        'customer_number',
        'user_id',
        'nama',
        'telepon',
        'whatsapp_number',
        'email',
        'alamat',
        'paket_id',
        'router_id',
        'mikrotik_username',
        'status',
        'saldo',
        'auto_renew',
        'suspension_source',
        'status_reason',
        'suspended_at',
        'suspended_by_id',
        'tanggal_aktif',
        'joined_at',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => CustomerStatus::class,
            'saldo' => 'integer',
            'auto_renew' => 'boolean',
            'suspension_source' => SuspensionSource::class,
            'suspended_at' => 'datetime',
            'tanggal_aktif' => 'date',
            'joined_at' => 'date',
            'archived_at' => 'datetime',
        ];
    }

    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class);
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function suspendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suspended_by_id');
    }

    public function tagihan(): HasMany
    {
        return $this->hasMany(Tagihan::class);
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function suspendLogs(): HasMany
    {
        return $this->hasMany(SuspendLog::class);
    }

    public function whatsappNotifications(): HasMany
    {
        return $this->hasMany(WhatsappNotification::class);
    }

    public function saldoMutations(): HasMany
    {
        return $this->hasMany(SaldoMutation::class);
    }

    /**
     * Nomor tujuan notifikasi WhatsApp, di-normalisasi ke format internasional
     * tanpa tanda baca (mis. 0812 -> 62812, +62 812 -> 62812).
     */
    public function whatsappTarget(): ?string
    {
        return static::normalizePhoneNumber($this->whatsapp_number ?? $this->telepon);
    }

    public static function normalizePhoneNumber(?string $number): ?string
    {
        if ($number === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $number) ?? '';

        if ($digits === '') {
            return null;
        }

        // Awalan 0 lokal -> 62, awalan 6200 -> 62.
        if (str_starts_with($digits, '0')) {
            $digits = '62'.ltrim(substr($digits, 1), '0');
        }

        return $digits === '' ? null : $digits;
    }
}
