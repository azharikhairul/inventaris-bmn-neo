<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class RealisasiAset extends Model
{
    use HasFactory;

    protected $table = 'realisasi_asets';

    protected $guarded = ['id'];

    protected $casts = [
        'subtotal' => 'decimal:2',
    ];

    public function realisasi()
    {
        return $this->belongsTo(Realisasi::class);
    }

    public function aset()
    {
        return $this->belongsTo(Aset::class);
    }

    public function realisasiItems()
    {
        return $this->hasMany(RealisasiItem::class);
    }

    public function hitungSubtotal(): void
    {
        $subtotal = $this->realisasiItems()->sum('jumlah');
        $this->update(['subtotal' => $subtotal]);
    }

    /**
     * Get array of stored photo paths
     */
    public function getFotoListAttribute(): array
    {
        $val = $this->foto_dokumentasi;
        if (empty($val)) {
            return [];
        }
        if (is_array($val)) {
            return $val;
        }
        if (is_string($val)) {
            $decoded = json_decode($val, true);
            if (is_array($decoded)) {
                return $decoded;
            }

            return [$val];
        }

        return [];
    }

    /**
     * Get array of public URLs for the photos
     */
    public function getFotoUrlsAttribute(): array
    {
        $list = $this->foto_list;
        $urls = [];
        foreach ($list as $path) {
            $urls[] = Storage::url($path);
        }

        return $urls;
    }
}
