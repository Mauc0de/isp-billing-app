<?php

namespace App\Models;

use App\Enums\RouterSuspendMethod;
use App\Enums\SuspendAction;
use App\Enums\SuspendLogStatus;
use App\Enums\SuspensionSource;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Audit trail setiap aksi suspend/reaktivasi.
 *
 * Tabel ini sengaja ditulis di attempts = single, tersimpan apa pun hasilnya,
 * karena Investigasi suspend salah selalu butuh bukti percobaan yang gagal.
 */
class SuspendLog extends Model
{
    use BelongsToTenant;
    use HasFactory, HasUlids;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'router_id',
        'invoice_id',
        'performed_by_id',
        'action',
        'source',
        'method',
        'status',
        'reason',
        'error',
        'context',
        'performed_at',
    ];

    protected function casts(): array
    {
        return [
            'action' => SuspendAction::class,
            'source' => SuspensionSource::class,
            'method' => RouterSuspendMethod::class,
            'status' => SuspendLogStatus::class,
            'context' => 'array',
            'performed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_id');
    }

    public function whatsappNotifications(): HasMany
    {
        return $this->hasMany(WhatsappNotification::class);
    }

    public function succeeded(): bool
    {
        return $this->status === SuspendLogStatus::Succeeded;
    }
}
