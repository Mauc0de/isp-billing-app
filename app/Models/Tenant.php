<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kode',
        'alamat',
        'telepon',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function pelanggan(): HasMany
    {
        return $this->hasMany(Pelanggan::class);
    }

    public function paket(): HasMany
    {
        return $this->hasMany(Paket::class);
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
