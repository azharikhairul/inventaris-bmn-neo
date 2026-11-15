<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Realisasi extends Model
{
    use HasFactory;

    protected $table = 'realisasis';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_pengajuan' => 'date',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'total_realisasi' => 'decimal:2',
        'stok_masuk_processed' => 'boolean',
    ];

    public function satker()
    {
        return $this->belongsTo(Satker::class);
    }

    public function permintaan()
    {
        return $this->belongsTo(Permintaan::class);
    }

    public function realisasiAsets()
    {
        return $this->hasMany(RealisasiAset::class);
    }

    public function distribusi()
    {
        return $this->hasOne(SupplyDistribusi::class, 'realisasi_id');
    }

    public function supplyMutasis()
    {
        return $this->hasMany(SupplyMutasi::class, 'realisasi_id');
    }

    public function hitungTotalRealisasi(): void
    {
        $this->unsetRelation('realisasiAsets');
        $total = 0;
        foreach ($this->realisasiAsets as $ra) {
            $ra->hitungSubtotal();
            $total += (float) $ra->subtotal;
        }

        $this->update(['total_realisasi' => $total]);
    }

    /**
     * Otomatis tambah stok persediaan jika realisasi alur Supply berstatus Selesai.
     */
    public function syncStokPersediaanMasuk(): void
    {
        if ($this->jenis !== 'Supply' || $this->status !== 'Selesai') {
            return;
        }

        $this->unsetRelation('realisasiAsets');
        $this->load(['realisasiAsets.realisasiItems']);

        // Jika sudah pernah diproses sebelumnya (misal update), revert mutasi lama terlebih dahulu
        if ($this->stok_masuk_processed) {
            $existingMutasis = SupplyMutasi::where('realisasi_id', $this->id)->get();
            foreach ($existingMutasis as $mutasi) {
                if ($mutasi->supply) {
                    $mutasi->supply->update([
                        'stok' => max(0, $mutasi->supply->stok - $mutasi->jumlah),
                    ]);
                }
                $mutasi->delete();
            }
        }

        foreach ($this->realisasiAsets as $ra) {
            foreach ($ra->realisasiItems as $ri) {
                $volume = (int) $ri->volume;
                if ($volume <= 0) {
                    continue;
                }

                $namaBarang = trim($ri->uraian ?: ($ra->nama_aset ?: 'Barang Persediaan'));
                $satuan = trim($ri->satuan ?: 'unit');
                $harga = (float) $ri->harga_satuan;

                // 1. Cari exact match nama_barang
                $supply = Supply::whereRaw('LOWER(TRIM(nama_barang)) = ?', [strtolower($namaBarang)])->first();

                // 2. Jika tidak ditemukan dan ada nama_aset berbeda, cari nama_aset
                if (! $supply && $ra->nama_aset && strtolower(trim($ra->nama_aset)) !== strtolower($namaBarang)) {
                    $supply = Supply::whereRaw('LOWER(TRIM(nama_barang)) = ?', [strtolower(trim($ra->nama_aset))])->first();
                }

                // 3. Cari dengan LIKE namaBarang
                if (! $supply) {
                    $supply = Supply::where('nama_barang', 'like', "%{$namaBarang}%")->first();
                }

                // 4. Cari partial match dengan semua list
                if (! $supply) {
                    $allSupplies = Supply::all();
                    foreach ($allSupplies as $s) {
                        if (stripos($namaBarang, $s->nama_barang) !== false || stripos($s->nama_barang, $namaBarang) !== false) {
                            $supply = $s;
                            break;
                        }
                    }
                }

                // 5. Jika barang belum pernah ada di master persediaan, daftarkan otomatis
                if (! $supply) {
                    $prefix = 'SUP-'.date('y');
                    $lastId = Supply::max('id') ?? 0;
                    $kodeBarang = $prefix.'-'.str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
                    while (Supply::where('kode_barang', $kodeBarang)->exists()) {
                        $lastId++;
                        $kodeBarang = $prefix.'-'.str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
                    }

                    $supply = Supply::create([
                        'kode_barang' => $kodeBarang,
                        'nama_barang' => $namaBarang,
                        'kategori' => 'Alat Tulis Kantor (ATK)',
                        'satuan' => $satuan ?: 'Pcs',
                        'stok' => 0,
                        'stok_minimum' => 5,
                        'spesifikasi' => ($ra->nama_aset && $ra->nama_aset !== $namaBarang) ? $ra->nama_aset : null,
                        'harga_estimasi' => $harga > 0 ? $harga : null,
                    ]);
                }

                $keterangan = 'Pengadaan dari Realisasi Selesai No. FPA: '.($this->no_fpa ?: '-')." ({$this->pekerjaan})";
                $supply->tambahStok(
                    jumlah: $volume,
                    keterangan: $keterangan,
                    userId: auth()->id(),
                    realisasiId: $this->id
                );
            }
        }

        $this->update(['stok_masuk_processed' => true]);
    }
}
