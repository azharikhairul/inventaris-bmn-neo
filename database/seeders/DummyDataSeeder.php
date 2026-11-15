<?php

namespace Database\Seeders;

use App\Models\Aset;
use App\Models\Permintaan;
use App\Models\PermintaanAset;
use App\Models\RabItem;
use App\Models\Realisasi;
use App\Models\RealisasiAset;
use App\Models\RealisasiItem;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Check if there are assets available
        $asets = Aset::inRandomOrder()->limit(500)->get();

        if ($asets->isEmpty()) {
            $this->command->warn('Tidak ada data Aset di database. Silakan import Aset terlebih dahulu.');

            return;
        }

        $jenisPekerjaanList = ['Perbaikan AC', 'Servis Kendaraan', 'Perbaikan Gedung', 'Penggantian Suku Cadang', 'Maintenance IT', 'Renovasi Ruangan', 'Perbaikan Meubeler', 'Pemasangan CCTV', 'Perbaikan Lift', 'Service Genset'];
        $kondisiAsetList = ['Baik', 'Rusak Ringan', 'Rusak Berat'];
        $vendors = ['CV. Makmur Jaya', 'PT. Bintang Terang', 'CV. Teknisi Mandiri', 'Budi Service', 'PT. Global Konstruksi', 'Toko Bangunan Sejahtera'];

        $totalPermintaan = 150;
        $this->command->info("Membuat $totalPermintaan data Permintaan dummy beserta Realisasinya...");

        DB::beginTransaction();
        try {
            for ($i = 0; $i < $totalPermintaan; $i++) {
                // Generate date within the last 12 months
                $createdAt = Carbon::now()->subDays(rand(0, 360));

                // 1. Create Permintaan
                $jenisPekerjaan = $faker->randomElement($jenisPekerjaanList);
                $jenisKategori = $faker->randomElement(['Maintenance', 'Supply']);

                $permintaan = Permintaan::create([
                    'pekerjaan' => $jenisPekerjaan.' '.$faker->city,
                    'jenis' => $jenisKategori,
                    'total_rab' => 0,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                // Determine number of assets in this permintaan (1 to 3)
                $numAsets = rand(1, 3);
                $totalRab = 0;

                $selectedAsets = $asets->random($numAsets);

                foreach ($selectedAsets as $aset) {
                    $kondisi = $faker->randomElement($kondisiAsetList);
                    $deskripsi = $faker->sentence(6);

                    $permintaanAset = PermintaanAset::create([
                        'permintaan_id' => $permintaan->id,
                        'aset_id' => $aset->id,
                        'deskripsi_kerusakan' => $deskripsi,
                        'kondisi_aset' => $kondisi,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);

                    // Determine RAB items (1 to 4)
                    $numRabItems = rand(1, 4);
                    for ($r = 0; $r < $numRabItems; $r++) {
                        $vol = rand(1, 5);
                        $harga = (rand(10, 500) * 1000);
                        $jumlah = $vol * $harga;

                        RabItem::create([
                            'permintaan_aset_id' => $permintaanAset->id,
                            'uraian' => $faker->words(3, true),
                            'volume' => $vol,
                            'satuan' => $faker->randomElement(['unit', 'bh', 'set', 'ls']),
                            'harga_satuan' => $harga,
                            'jumlah' => $jumlah,
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                        ]);

                        $totalRab += $jumlah;
                    }
                }

                // Update Total RAB
                $permintaan->update(['total_rab' => $totalRab]);

                // 2. Create Realisasi (80% chance to have realisasi)
                if (rand(1, 100) <= 80) {
                    // Status: Selesai (70%) or siap realisasi (30%)
                    $status = rand(1, 100) <= 70 ? 'Selesai' : 'siap realisasi';
                    $tanggalMulai = (clone $createdAt)->addDays(rand(1, 10));
                    $tanggalSelesai = $status === 'Selesai' ? (clone $tanggalMulai)->addDays(rand(1, 20)) : null;

                    $realisasi = Realisasi::create([
                        'permintaan_id' => $permintaan->id,
                        'no_fpa' => 'FPA-'.$createdAt->format('Y').'-'.str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                        'vendor' => $faker->randomElement($vendors),
                        'pekerjaan' => $permintaan->pekerjaan,
                        'jenis' => $permintaan->jenis,
                        'tanggal_pengajuan' => $createdAt->format('Y-m-d'),
                        'tanggal_mulai' => $tanggalMulai->format('Y-m-d'),
                        'tanggal_selesai' => $tanggalSelesai ? $tanggalSelesai->format('Y-m-d') : null,
                        'total_realisasi' => 0,
                        'status' => $status,
                        'created_at' => $tanggalMulai,
                        'updated_at' => $tanggalSelesai ?? $tanggalMulai,
                    ]);

                    $totalRealisasi = 0;

                    foreach ($permintaan->permintaanAsets as $pa) {
                        $aset = $pa->aset;
                        $realisasiAset = RealisasiAset::create([
                            'realisasi_id' => $realisasi->id,
                            'aset_id' => $aset->id,
                            'nama_aset' => $aset->nama_barang,
                            'kode_inventaris' => ($aset->kode_barang ?? '-').' / '.($aset->nup ?? '-'),
                            'deskripsi_kerusakan' => $pa->deskripsi_kerusakan,
                            'subtotal' => 0,
                            'created_at' => $realisasi->created_at,
                            'updated_at' => $realisasi->updated_at,
                        ]);

                        $subtotalRealisasi = 0;

                        foreach ($pa->rabItems as $rab) {
                            // Actual price is randomly +- 10% from RAB price or exactly the same
                            $modifier = rand(90, 110) / 100;
                            $hargaRealisasi = round($rab->harga_satuan * $modifier / 1000) * 1000;
                            $jumlahRealisasi = $rab->volume * $hargaRealisasi;

                            RealisasiItem::create([
                                'realisasi_aset_id' => $realisasiAset->id,
                                'uraian' => $rab->uraian,
                                'volume' => $rab->volume,
                                'satuan' => $rab->satuan,
                                'harga_satuan' => $hargaRealisasi,
                                'jumlah' => $jumlahRealisasi,
                                'created_at' => $realisasi->created_at,
                                'updated_at' => $realisasi->updated_at,
                            ]);

                            $subtotalRealisasi += $jumlahRealisasi;
                        }

                        $realisasiAset->update(['subtotal' => $subtotalRealisasi]);
                        $totalRealisasi += $subtotalRealisasi;
                    }

                    $realisasi->update(['total_realisasi' => $totalRealisasi]);
                }
            }
            DB::commit();
            $this->command->info('Data dummy berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Terjadi kesalahan: '.$e->getMessage());
        }
    }
}
