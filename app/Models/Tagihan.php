<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tagihan extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'pelanggan_id',
        'nomor_tagihan',
        'jumlah',
        'tanggal_terbit',
        'jatuh_tempo',
        'tanggal_bayar',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_terbit' => 'date',
        'jatuh_tempo' => 'date',
        'tanggal_bayar' => 'date',
        'jumlah' => 'integer',
    ];

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
