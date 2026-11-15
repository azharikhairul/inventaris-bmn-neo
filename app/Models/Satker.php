<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Satker extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Users belonging to this satker.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Permintaan records belonging to this satker.
     */
    public function permintaans()
    {
        return $this->hasMany(Permintaan::class);
    }

    /**
     * Realisasi records belonging to this satker.
     */
    public function realisasis()
    {
        return $this->hasMany(Realisasi::class);
    }

    /**
     * Supplies/inventory items belonging to this satker.
     */
    public function supplies()
    {
        return $this->hasMany(Supply::class);
    }

    /**
     * Distribution records belonging to this satker.
     */
    public function distribusis()
    {
        return $this->hasMany(SupplyDistribusi::class);
    }

    /**
     * Aset records associated with this satker via kode_satker.
     */
    public function asets()
    {
        return $this->hasMany(Aset::class, 'kode_satker', 'kode_satker');
    }

    /**
     * Check if satker is at provincial level.
     */
    public function isProvinsi(): bool
    {
        return strtolower($this->tingkat ?? '') === 'provinsi';
    }

    /**
     * Check if satker is at regency/city level.
     */
    public function isKabupaten(): bool
    {
        return ! $this->isProvinsi();
    }

    /**
     * Get the display region / wilayah label.
     */
    public function getWilayahNamaAttribute(): string
    {
        if ($this->isProvinsi()) {
            return ($this->kabupaten_kota && $this->kabupaten_kota !== 'Kota Gorontalo')
                ? $this->kabupaten_kota
                : 'Provinsi Gorontalo';
        }

        return $this->kabupaten_kota ?? $this->nama_satker;
    }
}
