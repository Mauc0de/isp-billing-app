<?php

namespace App\Models;

use App\Enums\WhatsappProvider;
use App\Enums\WhatsappStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappNotification extends Model
{
    use BelongsToTenant;
    use HasFactory, HasUlids;

    /**
     * Nama class dalam bentuk jamak akan menjadi "whatsapp_notifications",
     * sementara tabelnya disingkat "wa_notifications".
     */
    protected $table = 'wa_notifications';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'invoice_id',
        'suspend_log_id',
        'to_number',
        'message',
        'provider',
        'status',
        'provider_message_id',
        'error',
        'attempts',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'provider' => WhatsappProvider::class,
            'status' => WhatsappStatus::class,
            'attempts' => 'integer',
            'sent_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function suspendLog(): BelongsTo
    {
        return $this->belongsTo(SuspendLog::class);
    }
}
