<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permintaan extends Model
{
    use HasFactory;

    protected $table = 'permintaans';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal' => 'date',
        'total_rab' => 'decimal:2',
    ];

    /**
     * Satker belonging to this permintaan.
     */
    public function satker()
    {
        return $this->belongsTo(Satker::class);
    }

    /**
     * Get all permintaan_aset entries for this permintaan.
     */
    public function permintaanAsets()
    {
        return $this->hasMany(PermintaanAset::class);
    }

    /**
     * Get all realisasi entries for this permintaan.
     */
    public function realisasis()
    {
        return $this->hasMany(Realisasi::class);
    }

    public function realisasi()
    {
        return $this->hasOne(Realisasi::class)->latestOfMany();
    }

    public function distribusi()
    {
        return $this->hasOne(SupplyDistribusi::class, 'permintaan_id');
    }

    /**
     * Check if permintaan is of type Supply.
     */
    public function isSupply(): bool
    {
        return strtolower($this->jenis ?? '') === 'supply';
    }

    /**
     * Check if permintaan is of type Maintenance.
     */
    public function isMaintenance(): bool
    {
        return ! $this->isSupply();
    }

    /**
     * Recalculate total_rab from all related RAB items.
     */
    public function hitungTotalRab(): void
    {
        $total = $this->permintaanAsets()
            ->with('rabItems')
            ->get()
            ->flatMap(fn ($pa) => $pa->rabItems)
            ->sum('jumlah');

        $this->update(['total_rab' => $total]);
    }
}
