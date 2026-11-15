<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aset extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_buku_pertama' => 'date',
        'tanggal_perolehan' => 'date',
        'tanggal_pengapusan' => 'date',
        'tanggal_psp' => 'date',
        'nilai_perolehan_pertama' => 'decimal:2',
        'nilai_mutasi' => 'decimal:2',
        'nilai_perolehan' => 'decimal:2',
        'nilai_penyusutan' => 'decimal:2',
        'nilai_buku' => 'decimal:2',
        'luas_tanah_seluruhnya' => 'decimal:2',
        'luas_tanah_untuk_bangunan' => 'decimal:2',
        'luas_tanah_untuk_sarana' => 'decimal:2',
        'luas_lahan_kosong' => 'decimal:2',
        'luas_bangunan' => 'decimal:2',
        'luas_tapak_bangunan' => 'decimal:2',
        'luas_pemanfaatan' => 'decimal:2',
        'jumlah_lantai' => 'integer',
        'jumlah_foto' => 'integer',
        'umur_aset' => 'integer',
        'sbsk' => 'decimal:2',
        'optimalisasi' => 'decimal:2',
    ];

    /**
     * Satker pemilik aset ini.
     */
    public function satker()
    {
        return $this->belongsTo(Satker::class, 'kode_satker', 'kode_satker');
    }

    /**
     * Permintaan records associated with this asset.
     */
    public function permintaanAsets()
    {
        return $this->hasMany(PermintaanAset::class, 'aset_id');
    }

    /**
     * Realisasi records associated with this asset.
     */
    public function realisasiAsets()
    {
        return $this->hasMany(RealisasiAset::class, 'aset_id');
    }

    /**
     * Get latest tanggal permintaan from realisasi or permintaan.
     */
    public function getLatestTanggalPermintaanAttribute()
    {
        if ($this->relationLoaded('realisasiAsets')) {
            $latestRealisasi = $this->realisasiAsets
                ->map(fn ($ra) => $ra->realisasi)
                ->filter()
                ->sortByDesc(fn ($r) => $r->tanggal_pengajuan ?? $r->created_at)
                ->first();

            if ($latestRealisasi) {
                $date = $latestRealisasi->tanggal_pengajuan ?? $latestRealisasi->created_at;
                if ($date) {
                    return $date->translatedFormat('d M Y');
                }
            }
        }

        if ($this->relationLoaded('permintaanAsets')) {
            $latestPermintaan = $this->permintaanAsets
                ->map(fn ($pa) => $pa->permintaan)
                ->filter()
                ->sortByDesc('created_at')
                ->first();

            if ($latestPermintaan && $latestPermintaan->created_at) {
                return $latestPermintaan->created_at->translatedFormat('d M Y');
            }
        }

        $ra = $this->realisasiAsets()->with('realisasi')->latest('updated_at')->first();
        if ($ra && $ra->realisasi) {
            $date = $ra->realisasi->tanggal_pengajuan ?? $ra->realisasi->created_at;
            if ($date) {
                return $date->translatedFormat('d M Y');
            }
        }

        $pa = $this->permintaanAsets()->with('permintaan')->latest('updated_at')->first();
        if ($pa && $pa->permintaan && $pa->permintaan->created_at) {
            return $pa->permintaan->created_at->translatedFormat('d M Y');
        }

        return null;
    }

    /**
     * Formatted / Clean Kode Barang (strip trailing .0)
     */
    public function getCleanKodeBarangAttribute()
    {
        if (empty($this->kode_barang)) {
            return '-';
        }

        return preg_replace('/\.0+$/', '', (string) $this->kode_barang);
    }

    /**
     * Formatted / Clean NUP (strip trailing .0)
     */
    public function getCleanNupAttribute()
    {
        if (empty($this->nup)) {
            return '-';
        }

        return preg_replace('/\.0+$/', '', (string) $this->nup);
    }

    /**
     * Clean Room / Location (strip leading '- - ')
     */
    public function getCleanLokasiRuangAttribute()
    {
        if (empty($this->lokasi_ruang)) {
            return 'Belum berlokasi';
        }
        $cleaned = trim(preg_replace('/^[-–—\s]+/', '', (string) $this->lokasi_ruang));

        return empty($cleaned) ? 'Belum berlokasi' : $cleaned;
    }

    /**
     * Check if asset is Rusak Berat
     */
    public function getIsRusakBeratAttribute()
    {
        return strtolower(trim($this->kondisi ?? '')) === 'rusak berat';
    }

    /**
     * Check if asset is Rusak Ringan
     */
    public function getIsRusakRinganAttribute()
    {
        return strtolower(trim($this->kondisi ?? '')) === 'rusak ringan';
    }

    /**
     * Check if asset is Baik
     */
    public function getIsBaikAttribute()
    {
        return strtolower(trim($this->kondisi ?? '')) === 'baik';
    }

    /**
     * Eligibility for maintenance (Aset Rusak Berat tidak boleh dimaintenance)
     */
    public function getCanMaintenanceAttribute()
    {
        return ! $this->is_rusak_berat;
    }

    /**
     * Scope for searching assets
     */
    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        $terms = array_unique(array_filter([
            $search,
            strcasecmp(trim($search), 'ac') === 0 ? 'A.C' : null,
            strcasecmp(trim($search), 'a.c') === 0 || strcasecmp(trim($search), 'a.c.') === 0 ? 'AC' : null,
        ]));

        return $query->where(function ($q) use ($terms) {
            foreach ($terms as $idx => $term) {
                $boolean = $idx === 0 ? 'and' : 'or';
                $q->where(function ($sub) use ($term) {
                    $sub->where('nama_barang', 'like', "%{$term}%")
                        ->orWhere('kode_barang', 'like', "%{$term}%")
                        ->orWhere('nup', 'like', "%{$term}%")
                        ->orWhere('merk', 'like', "%{$term}%")
                        ->orWhere('tipe', 'like', "%{$term}%")
                        ->orWhere('jenis_bmn', 'like', "%{$term}%");
                }, null, null, $boolean);
            }
        });
    }
}
