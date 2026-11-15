<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class SupplyDistribusi extends Model
{
    use HasFactory;

    protected $table = 'supply_distribusis';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_bast' => 'date',
        'uploaded_at' => 'datetime',
    ];

    public function permintaan(): BelongsTo
    {
        return $this->belongsTo(Permintaan::class, 'permintaan_id');
    }

    public function supplyPermintaan(): BelongsTo
    {
        return $this->belongsTo(SupplyPermintaan::class, 'supply_permintaan_id');
    }

    public function realisasi(): BelongsTo
    {
        return $this->belongsTo(Realisasi::class, 'realisasi_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplyDistribusiItem::class);
    }

    public function mutasis(): HasMany
    {
        return $this->hasMany(SupplyMutasi::class, 'supply_distribusi_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFileUrlAttribute(): ?string
    {
        $path = $this->file_bast_ttd
            ?: $this->realisasi?->bast_file
            ?: $this->realisasi?->kuitansi_file
            ?: $this->permintaan?->realisasi?->bast_file
            ?: $this->permintaan?->realisasi?->kuitansi_file;

        if (! $path) {
            return null;
        }

        return Storage::url($path);
    }

    /**
     * Helper to generate human-friendly BAST number.
     * Format: BAST-SUP/{TAHUN}/{BULAN}/{URUT}
     */
    public static function generateNomorBast(): string
    {
        $prefix = 'BAST-SUP/'.date('Y/m').'/';
        $last = static::where('nomor_bast', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        $seq = 1;
        if ($last) {
            $parts = explode('/', $last->nomor_bast);
            $seq = ((int) end($parts)) + 1;
        }

        return $prefix.str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
