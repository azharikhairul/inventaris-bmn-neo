<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supply extends Model
{
    use HasFactory;

    protected $table = 'supplies';

    protected $guarded = ['id'];

    protected $casts = [
        'stok' => 'integer',
        'stok_minimum' => 'integer',
        'harga_estimasi' => 'decimal:2',
    ];

    /**
     * Satker pemilik stok persediaan ini.
     */
    public function satker()
    {
        return $this->belongsTo(Satker::class);
    }

    /**
     * Mutasi riwayat stok masuk & keluar.
     */
    public function mutasis(): HasMany
    {
        return $this->hasMany(SupplyMutasi::class)->latest('tanggal')->latest('id');
    }

    /**
     * Item permintaan yang memuat barang ini.
     */
    public function permintaanItems(): HasMany
    {
        return $this->hasMany(SupplyPermintaanItem::class);
    }

    /**
     * Item distribusi yang memuat barang ini.
     */
    public function distribusiItems(): HasMany
    {
        return $this->hasMany(SupplyDistribusiItem::class);
    }

    /**
     * Tambah stok persediaan (stok masuk).
     */
    public function tambahStok(int $jumlah, ?string $keterangan = null, ?int $userId = null, ?int $realisasiId = null): SupplyMutasi
    {
        $stokSebelum = $this->stok;
        $stokSesudah = $stokSebelum + $jumlah;

        $this->update(['stok' => $stokSesudah]);

        return $this->mutasis()->create([
            'realisasi_id' => $realisasiId,
            'jenis' => 'masuk',
            'jumlah' => $jumlah,
            'stok_sebelum' => $stokSebelum,
            'stok_sesudah' => $stokSesudah,
            'tanggal' => now()->toDateString(),
            'keterangan' => $keterangan ?? 'Penambahan stok / barang masuk',
            'user_id' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Kurangi stok persediaan (stok keluar untuk distribusi).
     */
    public function kurangiStok(int $jumlah, ?SupplyDistribusi $distribusi = null, ?string $keterangan = null, ?int $userId = null): SupplyMutasi
    {
        $stokSebelum = $this->stok;
        $stokSesudah = max(0, $stokSebelum - $jumlah);

        $this->update(['stok' => $stokSesudah]);

        return $this->mutasis()->create([
            'supply_distribusi_id' => $distribusi?->id,
            'jenis' => 'keluar',
            'jumlah' => $jumlah,
            'stok_sebelum' => $stokSebelum,
            'stok_sesudah' => $stokSesudah,
            'tanggal' => now()->toDateString(),
            'keterangan' => $keterangan ?? ($distribusi ? "Distribusi BAST #{$distribusi->nomor_bast}" : 'Pengeluaran barang'),
            'user_id' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Indikator status stok aman / menipis / habis.
     */
    public function getStatusStokAttribute(): string
    {
        if ($this->stok <= 0) {
            return 'Habis';
        }
        if ($this->stok <= $this->stok_minimum) {
            return 'Menipis';
        }

        return 'Tersedia';
    }
}
