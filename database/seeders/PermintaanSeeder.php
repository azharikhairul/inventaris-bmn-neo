<?php

namespace Database\Seeders;

use App\Models\Aset;
use App\Models\Permintaan;
use Illuminate\Database\Seeder;

class PermintaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $asets = Aset::take(4)->get();
        $aset1 = $asets->get(0);
        $aset2 = $asets->get(1);
        $aset3 = $asets->get(2);

        // Permintaan 1: Maintenance
        $p1 = Permintaan::create([
            'pekerjaan' => 'Pemeliharaan AC Ruang Rapat Lt. 2 dan Ruang Pimpinan',
            'jenis' => 'Maintenance',
            'total_rab' => 0,
        ]);

        $pa1 = $p1->permintaanAsets()->create([
            'aset_id' => $aset1?->id,
            'deskripsi_kerusakan' => 'Unit AC tidak dingin dan mengeluarkan suara bising.',
        ]);
        $pa1->rabItems()->createMany([
            ['uraian' => 'Pembersihan Filter & Evaporator', 'volume' => 2, 'satuan' => 'unit', 'harga_satuan' => 150000, 'jumlah' => 300000],
            ['uraian' => 'Pengisian Freon R32', 'volume' => 1, 'satuan' => 'paket', 'harga_satuan' => 350000, 'jumlah' => 350000],
        ]);

        if ($aset2) {
            $pa2 = $p1->permintaanAsets()->create([
                'aset_id' => $aset2->id,
                'deskripsi_kerusakan' => 'Perlu service berkala dan pencucian rutin.',
            ]);
            $pa2->rabItems()->createMany([
                ['uraian' => 'Service Cleaning Fan & Motor', 'volume' => 1, 'satuan' => 'unit', 'harga_satuan' => 250000, 'jumlah' => 250000],
            ]);
        }
        $p1->hitungTotalRab();

        // Permintaan 2: Supply
        $p2 = Permintaan::create([
            'pekerjaan' => 'Pengadaan ATK dan Perlengkapan Komputer Divisi Statistik',
            'jenis' => 'Supply',
            'total_rab' => 0,
        ]);
        $pa3 = $p2->permintaanAsets()->create([
            'aset_id' => $aset3?->id,
            'deskripsi_kerusakan' => 'Kebutuhan cartridge toner dan kertas untuk pencetakan laporan bulanan.',
        ]);
        $pa3->rabItems()->createMany([
            ['uraian' => 'Toner Printer HP LaserJet', 'volume' => 2, 'satuan' => 'buah', 'harga_satuan' => 650000, 'jumlah' => 1300000],
            ['uraian' => 'Kertas A4 80gr PaperOne', 'volume' => 10, 'satuan' => 'rim', 'harga_satuan' => 55000, 'jumlah' => 550000],
        ]);
        $p2->hitungTotalRab();
    }
}
