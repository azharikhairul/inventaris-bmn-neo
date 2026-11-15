<?php

namespace Tests\Feature;

use App\Models\Aset;
use App\Models\Permintaan;
use App\Models\Realisasi;
use Tests\TestCase;

class PermintaanCrudTest extends TestCase
{
    public function test_can_view_index_page(): void
    {
        $response = $this->get(route('permintaan.index'));
        $response->assertStatus(200);
        $response->assertSee('Daftar Permintaan');
    }

    public function test_can_view_create_page(): void
    {
        $response = $this->get(route('permintaan.create', ['type' => 'maintenance']));
        $response->assertStatus(200);
        $response->assertSee('Permintaan');
        $response->assertSee('rencana anggaran biaya (RAB)');
    }

    public function test_can_store_new_permintaan(): void
    {
        $aset = Aset::first();

        $payload = [
            'pekerjaan' => 'Pemeliharaan Lampu dan AC Ruang Rapat',
            'jenis' => 'Maintenance',
            'asetList' => [
                [
                    'selectedAsset' => $aset ? $aset->id : null,
                    'deskripsi_kerusakan' => 'Lampu kedap-kedip dan AC kurang dingin',
                    'rabItems' => [
                        [
                            'uraian' => 'Ganti Lampu LED Philips 18W',
                            'volume' => 4,
                            'satuan' => 'buah',
                            'hargaSatuan' => 75000,
                        ],
                        [
                            'uraian' => 'Cuci Filter AC',
                            'volume' => 2,
                            'satuan' => 'unit',
                            'hargaSatuan' => 100000,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->post(route('permintaan.store'), $payload);
        $response->assertRedirect(route('permintaan.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('permintaans', [
            'pekerjaan' => 'Pemeliharaan Lampu dan AC Ruang Rapat',
            'jenis' => 'Maintenance',
            'total_rab' => (4 * 75000) + (2 * 100000), // 500,000
        ]);
    }

    public function test_can_view_show_page(): void
    {
        $permintaan = Permintaan::first();
        if (! $permintaan) {
            $this->test_can_store_new_permintaan();
            $permintaan = Permintaan::first();
        }

        $response = $this->get(route('permintaan.show', $permintaan->id));
        $response->assertStatus(200);
        $response->assertSee('Rencana Anggaran Biaya (RAB)');
        $response->assertSee('Generate KAK');
    }

    public function test_can_view_edit_page(): void
    {
        $permintaan = Permintaan::first();
        if (! $permintaan) {
            $this->test_can_store_new_permintaan();
            $permintaan = Permintaan::first();
        }

        $response = $this->get(route('permintaan.edit', $permintaan->id));
        $response->assertStatus(200);
        $response->assertSee('Edit Permintaan');
        $response->assertSee('Kembali ke Daftar');
    }

    public function test_can_update_permintaan(): void
    {
        $permintaan = Permintaan::first();
        if (! $permintaan) {
            $this->test_can_store_new_permintaan();
            $permintaan = Permintaan::first();
        }

        $aset = Aset::where('kondisi', '!=', 'Rusak Berat')->first();

        $payload = [
            'pekerjaan' => 'Pemeliharaan Lengkap AC & Instalasi Listrik Updated',
            'jenis' => 'Maintenance',
            'asetList' => [
                [
                    'selectedAsset' => $aset ? $aset->id : 1,
                    'deskripsi_kerusakan' => 'Perbaikan instalasi kabel dan MCB',
                    'rabItems' => [
                        [
                            'uraian' => 'Penggantian MCB 16A Schneider',
                            'volume' => 3,
                            'satuan' => 'buah',
                            'hargaSatuan' => 120000,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->put(route('permintaan.update', $permintaan->id), $payload);
        $response->assertRedirect(route('permintaan.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('permintaans', [
            'id' => $permintaan->id,
            'pekerjaan' => 'Pemeliharaan Lengkap AC & Instalasi Listrik Updated',
            'total_rab' => 3 * 120000, // 360,000
        ]);
    }

    public function test_can_store_new_supply_permintaan(): void
    {
        $payload = [
            'pekerjaan' => 'Pengadaan Kertas HVS dan Tinta Printer',
            'jenis' => 'Supply',
            'asetList' => [
                [
                    'nama_barang' => 'Kertas HVS A4 80gr',
                    'deskripsi_kerusakan' => 'Kebutuhan administrasi triwulan II',
                    'rabItems' => [
                        [
                            'uraian' => 'Kertas HVS A4 80gr PaperOne',
                            'volume' => 10,
                            'satuan' => 'rim',
                            'hargaSatuan' => 55000,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->post(route('permintaan.store'), $payload);
        $response->assertRedirect(route('permintaan.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('permintaans', [
            'pekerjaan' => 'Pengadaan Kertas HVS dan Tinta Printer',
            'jenis' => 'Supply',
            'total_rab' => 10 * 55000,
        ]);
    }

    public function test_can_delete_permintaan(): void
    {
        $permintaan = Permintaan::create([
            'pekerjaan' => 'Permintaan Untuk Dihapus',
            'jenis' => 'Supply',
            'status' => 'Draft',
            'total_rab' => 100000,
        ]);

        $response = $this->delete(route('permintaan.destroy', $permintaan->id));
        $response->assertRedirect(route('permintaan.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('permintaans', [
            'id' => $permintaan->id,
        ]);
    }

    public function test_cannot_store_permintaan_with_rusak_berat_asset(): void
    {
        $aset = Aset::where('kondisi', 'Rusak Berat')->first();
        if ($aset) {
            $payload = [
                'pekerjaan' => 'Coba Pemeliharaan Aset Rusak Berat',
                'jenis' => 'Maintenance',
                'asetList' => [
                    [
                        'selectedAsset' => $aset->id,
                        'deskripsi_kerusakan' => 'Rusak parah',
                        'rabItems' => [
                            [
                                'uraian' => 'Servis total',
                                'volume' => 1,
                                'satuan' => 'unit',
                                'hargaSatuan' => 500000,
                            ],
                        ],
                    ],
                ],
            ];

            $response = $this->post(route('permintaan.store'), $payload);
            $response->assertSessionHasErrors('pekerjaan');
        }
    }

    public function test_cannot_store_permintaan_with_empty_rab_uraian(): void
    {
        $aset = Aset::where('kondisi', '!=', 'Rusak Berat')->first();

        $payload = [
            'pekerjaan' => 'Uji Validasi RAB Kosong',
            'jenis' => 'Maintenance',
            'asetList' => [
                [
                    'selectedAsset' => $aset ? $aset->id : 1,
                    'deskripsi_kerusakan' => 'Rusak',
                    'rabItems' => [
                        [
                            'uraian' => '   ',
                            'volume' => 1,
                            'satuan' => 'unit',
                            'hargaSatuan' => 100000,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->post(route('permintaan.store'), $payload);
        $response->assertSessionHasErrors('rab');
    }

    public function test_cannot_store_permintaan_with_empty_rab_list(): void
    {
        $aset = Aset::where('kondisi', '!=', 'Rusak Berat')->first();

        $payload = [
            'pekerjaan' => 'Uji Validasi Tanpa Item RAB',
            'jenis' => 'Maintenance',
            'asetList' => [
                [
                    'selectedAsset' => $aset ? $aset->id : 1,
                    'deskripsi_kerusakan' => 'Rusak',
                    'rabItems' => [],
                ],
            ],
        ];

        $response = $this->post(route('permintaan.store'), $payload);
        $response->assertSessionHasErrors('rab');
    }

    public function test_cannot_update_permintaan_with_empty_rab_uraian(): void
    {
        $permintaan = Permintaan::first();
        if (! $permintaan) {
            $this->test_can_store_new_permintaan();
            $permintaan = Permintaan::first();
        }

        $aset = Aset::where('kondisi', '!=', 'Rusak Berat')->first();

        $payload = [
            'pekerjaan' => 'Uji Update RAB Kosong',
            'jenis' => 'Maintenance',
            'asetList' => [
                [
                    'selectedAsset' => $aset ? $aset->id : 1,
                    'deskripsi_kerusakan' => 'Rusak',
                    'rabItems' => [
                        [
                            'uraian' => '',
                            'volume' => 1,
                            'satuan' => 'unit',
                            'hargaSatuan' => 50000,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->put(route('permintaan.update', $permintaan->id), $payload);
        $response->assertSessionHasErrors('rab');
    }

    public function test_cannot_store_permintaan_with_zero_or_empty_harga_satuan(): void
    {
        $aset = Aset::where('kondisi', '!=', 'Rusak Berat')->first();

        $payload = [
            'pekerjaan' => 'Uji Validasi Harga Satuan Nol',
            'jenis' => 'Maintenance',
            'asetList' => [
                [
                    'selectedAsset' => $aset ? $aset->id : 1,
                    'deskripsi_kerusakan' => 'Rusak',
                    'rabItems' => [
                        [
                            'uraian' => 'Perbaikan Komponen',
                            'volume' => 1,
                            'satuan' => 'unit',
                            'hargaSatuan' => 0,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->post(route('permintaan.store'), $payload);
        $response->assertSessionHasErrors('rab');
    }

    public function test_can_store_and_update_permintaan_with_custom_tanggal(): void
    {
        $aset = Aset::where('kondisi', '!=', 'Rusak Berat')->first();

        $payload = [
            'pekerjaan' => 'Pemeliharaan AC dengan Tanggal Kustom',
            'tanggal' => '2026-05-15',
            'jenis' => 'Maintenance',
            'asetList' => [
                [
                    'selectedAsset' => $aset ? $aset->id : 1,
                    'deskripsi_kerusakan' => 'Bocor',
                    'rabItems' => [
                        [
                            'uraian' => 'Servis AC',
                            'volume' => 1,
                            'satuan' => 'unit',
                            'hargaSatuan' => 150000,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->post(route('permintaan.store'), $payload);
        $response->assertRedirect(route('permintaan.index'));

        $this->assertDatabaseHas('permintaans', [
            'pekerjaan' => 'Pemeliharaan AC dengan Tanggal Kustom',
            'tanggal' => '2026-05-15',
        ]);

        $permintaan = Permintaan::where('pekerjaan', 'Pemeliharaan AC dengan Tanggal Kustom')->first();

        // Test update tanggal
        $updatePayload = [
            'pekerjaan' => 'Pemeliharaan AC dengan Tanggal Kustom Diperbarui',
            'tanggal' => '2026-06-20',
            'jenis' => 'Maintenance',
            'asetList' => [
                [
                    'selectedAsset' => $aset ? $aset->id : 1,
                    'deskripsi_kerusakan' => 'Bocor',
                    'rabItems' => [
                        [
                            'uraian' => 'Servis AC',
                            'volume' => 1,
                            'satuan' => 'unit',
                            'hargaSatuan' => 150000,
                        ],
                    ],
                ],
            ],
        ];

        $updateResponse = $this->put(route('permintaan.update', $permintaan->id), $updatePayload);
        $updateResponse->assertRedirect(route('permintaan.index'));

        $this->assertDatabaseHas('permintaans', [
            'id' => $permintaan->id,
            'tanggal' => '2026-06-20',
        ]);
    }

    public function test_supply_distribusi_create_with_realisasi_id(): void
    {
        $realisasi = Realisasi::where('jenis', 'Supply')->first();

        if (! $realisasi) {
            $permintaan = Permintaan::create([
                'user_id' => $this->testUser->id,
                'no_tiket' => 'REQ-TEST-SUP-BAST',
                'nama_pemohon' => 'Test Pemohon',
                'unit_kerja' => 'Tim IPDS',
                'jenis' => 'Supply',
                'status' => 'Selesai',
                'tanggal_permintaan' => now(),
            ]);

            $realisasi = Realisasi::create([
                'permintaan_id' => $permintaan->id,
                'jenis' => 'Supply',
                'pekerjaan' => 'Pengadaan Kertas & ATK Kantor',
                'status' => 'Selesai',
                'tanggal_selesai' => now(),
            ]);
        }

        $response = $this->get(route('supply.distribusi.create', [
            'realisasi_id' => $realisasi->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Buat Berita Acara Serah Terima (BAST)');
        $response->assertSee('distribusiCreateForm');
    }

    public function test_can_generate_kak_for_supply_with_new_template(): void
    {
        $permintaan = Permintaan::create([
            'user_id' => $this->testUser->id,
            'satker_id' => $this->testUser->satker_id,
            'no_tiket' => 'REQ-TEST-KAK-SUPPLY',
            'nama_pemohon' => 'Pegawai Supply',
            'unit_kerja' => 'Tim Pengolahan',
            'pekerjaan' => 'Pengadaan Kertas HVS dan Tinta Printer',
            'jenis' => 'Supply',
            'status' => 'Pending',
            'tanggal_permintaan' => now(),
            'total_rab' => 250000,
        ]);

        $response = $this->get(route('permintaan.kak', $permintaan->id));

        $response->assertStatus(200);
        // Memastikan konten Supply baru muncul
        $response->assertSee('BELANJA KEPERLUAN SEHARI-HARI PERKANTORAN (PERSEDIAAN)');
        $response->assertSee('UU Nomor 16 Tahun 1997 tentang Statistik');
        $response->assertSee('Peraturan Presiden Nomor 86 Tahun 2007');
        $response->assertSee('Sumber pembiayaan adalah dari DIPA');
        // Memastikan konten Maintenance TIDAK muncul di KAK Supply
        $response->assertDontSee('berupa pemeliharaan mesin');
    }

    public function test_can_generate_kak_for_maintenance_retains_maintenance_template(): void
    {
        $aset = Aset::first();

        $permintaan = Permintaan::create([
            'user_id' => $this->testUser->id,
            'satker_id' => $this->testUser->satker_id,
            'no_tiket' => 'REQ-TEST-KAK-MAINT',
            'nama_pemohon' => 'Pegawai Maintenance',
            'unit_kerja' => 'Umum',
            'pekerjaan' => 'Pemeliharaan AC Split',
            'jenis' => 'Maintenance',
            'status' => 'Pending',
            'tanggal_permintaan' => now(),
            'total_rab' => 500000,
        ]);

        $response = $this->get(route('permintaan.kak', $permintaan->id));

        $response->assertStatus(200);
        // Memastikan template Maintenance tetap utuh
        $response->assertSee('pemeliharaan peralatan dan mesin di lingkungan Kantor');
        $response->assertDontSee('BELANJA KEPERLUAN SEHARI-HARI PERKANTORAN (PERSEDIAAN)');
    }

    public function test_can_download_kak_pdf_for_supply(): void
    {
        $permintaan = Permintaan::create([
            'user_id' => $this->testUser->id,
            'satker_id' => $this->testUser->satker_id,
            'no_tiket' => 'REQ-TEST-KAK-PDF',
            'nama_pemohon' => 'Pegawai Supply PDF',
            'unit_kerja' => 'Tim IPDS',
            'pekerjaan' => 'Pengadaan ATK Pelatihan',
            'jenis' => 'Supply',
            'status' => 'Pending',
            'tanggal_permintaan' => now(),
            'total_rab' => 100000,
        ]);

        $response = $this->post(route('permintaan.kak.pdf', $permintaan->id), [
            'judul' => 'BELANJA KEPERLUAN SEHARI-HARI PERKANTORAN (PERSEDIAAN)',
            'penanggung_jawab_nama' => 'DWI ALWI ASTUTI, S.Tr.Stat.',
            'penanggung_jawab_nip' => '199801262021042001',
            'ppk_nama' => 'Sity Defriani Djou, S.E.',
            'ppk_nip' => '198402122008012009',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    public function test_bast_action_and_logic_only_available_for_supply_not_maintenance(): void
    {
        $maintPermintaan = Permintaan::create([
            'user_id' => $this->testUser->id,
            'satker_id' => $this->testUser->satker_id,
            'no_tiket' => 'REQ-TEST-MAINT-NOBAST',
            'nama_pemohon' => 'Pegawai Maintenance',
            'unit_kerja' => 'Umum',
            'pekerjaan' => 'Pemeliharaan AC Split Kantor',
            'jenis' => 'Maintenance',
            'status' => 'Pending',
            'tanggal_permintaan' => now(),
            'total_rab' => 200000,
        ]);

        $supplyPermintaan = Permintaan::create([
            'user_id' => $this->testUser->id,
            'satker_id' => $this->testUser->satker_id,
            'no_tiket' => 'REQ-TEST-SUPPLY-BAST',
            'nama_pemohon' => 'Pegawai Supply',
            'unit_kerja' => 'IPDS',
            'pekerjaan' => 'Pengadaan Kertas HVS Kantor',
            'jenis' => 'Supply',
            'status' => 'Pending',
            'tanggal_permintaan' => now(),
            'total_rab' => 150000,
        ]);

        // 1. Index Permintaan: BAST hanya muncul untuk Supply
        $resIndex = $this->get(route('permintaan.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee(route('permintaan.bast', $supplyPermintaan->id));
        $resIndex->assertDontSee(route('permintaan.bast', $maintPermintaan->id));

        // 2. Show Permintaan: Generate BAST hanya muncul untuk Supply
        $resShowMaint = $this->get(route('permintaan.show', $maintPermintaan->id));
        $resShowMaint->assertStatus(200);
        $resShowMaint->assertDontSee('Generate BAST');

        $resShowSupply = $this->get(route('permintaan.show', $supplyPermintaan->id));
        $resShowSupply->assertStatus(200);
        $resShowSupply->assertSee('Generate BAST');

        // 3. Akses generator BAST langsung: Maintenance harus 404
        $resBastMaint = $this->get(route('permintaan.bast', $maintPermintaan->id));
        $resBastMaint->assertStatus(404);

        $resBastPdfMaint = $this->get(route('permintaan.bast.pdf', $maintPermintaan->id));
        $resBastPdfMaint->assertStatus(404);

        // Supply bisa mengakses generator BAST & unduh PDF
        $resBastSupply = $this->get(route('permintaan.bast', $supplyPermintaan->id));
        $resBastSupply->assertStatus(200);

        $resBastPdfSupply = $this->get(route('permintaan.bast.pdf', $supplyPermintaan->id));
        $resBastPdfSupply->assertStatus(200);
        $this->assertEquals('application/pdf', $resBastPdfSupply->headers->get('content-type'));
    }
}
