<?php

namespace App\Models;

use App\Enums\RouterStatus;
use App\Enums\RouterSuspendMethod;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Router extends Model
{
    use BelongsToTenant;
    use HasFactory, HasUlids;

    protected $fillable = [
        'tenant_id',
        'name',
        'location',
        'vpn_ip',
        'api_port',
        'username',
        'password',
        'suspend_method',
        'address_list_name',
        'use_ssl',
        'status',
        'last_seen_at',
        'last_error',
        'notes',
        'is_active',
        'archived_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'suspend_method' => RouterSuspendMethod::class,
            'status' => RouterStatus::class,
            'use_ssl' => 'boolean',
            'is_active' => 'boolean',
            'last_seen_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * Nama address-list blokir yang dipakai ketika suspend_method = address_list.
     */
    public function blocklistName(): string
    {
        return $this->address_list_name ?: 'satak-blocklist';
    }

    public function pelanggans(): HasMany
    {
        return $this->hasMany(Pelanggan::class);
    }

    public function suspendLogs(): HasMany
    {
        return $this->hasMany(SuspendLog::class);
    }
}
