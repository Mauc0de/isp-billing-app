<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Konfigurasi per tenant dalam bentuk key-value.
 *
 * Nilai disimpan sebagai JSON supaya satu kolom bisa menyimpan bool, int,
 * string, maupun array. Akses typed lewat App\Settings\TenantSettings.
 */
class TenantSetting extends Model
{
    use BelongsToTenant;
    use HasFactory, HasUlids;

    /**
     * Nama class dalam bentuk jamak akan menjadi "tenant_settings", padahal
     * tabelnya memang sengaja dinamai "settings" supaya tidak berbenturan dengan
     * tabel bawaan Laravel dan enak dibaca di SQL mentah.
     */
    protected $table = 'settings';

    protected $fillable = [
        'tenant_id',
        'key',
        'value',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }
}
