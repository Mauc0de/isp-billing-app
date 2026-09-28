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

class Customer extends Model
{
    use BelongsToTenant;
    use HasFactory, HasUlids;

    protected $fillable = [
        'tenant_id',
        'package_id',
        'router_id',
        'mikrotik_username',
        'suspended_by_id',
        'customer_number',
        'name',
        'phone',
        'whatsapp_number',
        'email',
        'address',
        'status',
        'suspension_source',
        'status_reason',
        'suspended_at',
        'joined_at',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => CustomerStatus::class,
            'suspension_source' => SuspensionSource::class,
            'suspended_at' => 'datetime',
            'joined_at' => 'date',
            'archived_at' => 'datetime',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function suspendLogs(): HasMany
    {
        return $this->hasMany(SuspendLog::class);
    }

    public function whatsappNotifications(): HasMany
    {
        return $this->hasMany(WhatsappNotification::class);
    }

    /**
     * Nomor tujuan notifikasi WhatsApp, di-normalisasi ke format internasional
     * tanpa tanda baca (mis. 0812 -> 62812, +62 812 -> 62812).
     */
    public function whatsappTarget(): ?string
    {
        return static::normalizePhoneNumber($this->whatsapp_number ?? $this->phone);
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

    public function suspendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suspended_by_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
