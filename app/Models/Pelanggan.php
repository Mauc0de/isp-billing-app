<?php

namespace App\Models;

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
        'nama',
        'telepon',
        'email',
        'alamat',
        'paket_id',
        'status',
        'tanggal_aktif',
    ];

    protected $casts = [
        'tanggal_aktif' => 'date',
    ];

    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class);
    }

    public function tagihan(): HasMany
    {
        return $this->hasMany(Tagihan::class);
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }
}
