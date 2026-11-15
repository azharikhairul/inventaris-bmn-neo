<?php

namespace Database\Seeders;

use App\Models\Aset;
use App\Models\Permintaan;
use App\Models\Realisasi;
use Illuminate\Database\Seeder;

class RealisasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permintaan = Permintaan::first();
        $asets = Aset::take(2)->get();
        $aset1 = $asets->get(0);
        $aset2 = $asets->get(1);

        // Realisasi 1: Siap Realisasi
        $r1 = Realisasi::create([
            'permintaan_id' => $permintaan?->id,
            'no_fpa' => 'FPA/2023/10/0012',
            'vendor' => 'CV. Teknik Sejahtera',
            'pekerjaan' => 'Pemeliharaan AC Ruang Rapat Lt. 2 dan Ruang Pimpinan',
            'jenis' => 'Maintenance',
            'tanggal_pengajuan' => now()->subDays(5),
            'tanggal_mulai' => now()->subDays(2),
            'status' => 'siap realisasi',
            'total_realisasi' => 0,
        ]);

        $ra1 = $r1->realisasiAsets()->create([
            'aset_id' => $aset1?->id,
            'nama_aset' => $aset1 ? $aset1->nama_barang : 'Unit AC Split 2 PK',
            'kode_inventaris' => $aset1 ? ($aset1->kode_barang.' / '.$aset1->nup) : '3.05.02.01.003 / 1',
            'deskripsi_kerusakan' => 'Unit AC tidak dingin dan mengeluarkan suara bising.',
            'subtotal' => 0,
        ]);

        $ra1->realisasiItems()->createMany([
            ['uraian' => 'Pembersihan Filter & Evaporator', 'volume' => 2, 'satuan' => 'Unit', 'harga_satuan' => 150000, 'jumlah' => 300000],
            ['uraian' => 'Pengisian Freon R32', 'volume' => 1, 'satuan' => 'Paket', 'harga_satuan' => 350000, 'jumlah' => 350000],
        ]);
        $ra1->hitungSubtotal();
        $r1->hitungTotalRealisasi();

        // Realisasi 2: Selesai
        $r2 = Realisasi::create([
            'permintaan_id' => $permintaan?->id,
            'no_fpa' => 'FPA/2023/10/0005',
            'vendor' => 'PT. IT Solution Perkasa',
            'pekerjaan' => 'Pemeliharaan Perangkat IT Triwulan IV',
            'jenis' => 'Maintenance',
            'tanggal_pengajuan' => now()->subDays(15),
            'tanggal_mulai' => now()->subDays(10),
            'tanggal_selesai' => now()->subDays(4),
            'status' => 'Selesai',
            'total_realisasi' => 0,
        ]);

        $ra2 = $r2->realisasiAsets()->create([
            'aset_id' => $aset2?->id,
            'nama_aset' => $aset2 ? $aset2->nama_barang : 'PC Workstation All-in-One',
            'kode_inventaris' => $aset2 ? ($aset2->kode_barang.' / '.$aset2->nup) : '3.10.01.02.001 / 5',
            'deskripsi_kerusakan' => 'Penggantian Power Supply dan Upgrade RAM',
            'subtotal' => 0,
        ]);

        $ra2->realisasiItems()->createMany([
            ['uraian' => 'Power Supply Unit 500W Bronze', 'volume' => 1, 'satuan' => 'Unit', 'harga_satuan' => 650000, 'jumlah' => 650000],
            ['uraian' => 'RAM DDR4 8GB PC-2666', 'volume' => 2, 'satuan' => 'Unit', 'harga_satuan' => 450000, 'jumlah' => 900000],
            ['uraian' => 'Jasa Pemasangan & Maintenance', 'volume' => 1, 'satuan' => 'Ls', 'harga_satuan' => 150000, 'jumlah' => 150000],
        ]);
        $ra2->hitungSubtotal();
        $r2->hitungTotalRealisasi();
    }
}
