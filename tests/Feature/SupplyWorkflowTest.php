<?php

namespace Tests\Feature;

use App\Models\Permintaan;
use App\Models\Realisasi;
use App\Models\Supply;
use App\Models\SupplyDistribusi;
use App\Models\SupplyMutasi;
use App\Models\SupplyPermintaan;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SupplyWorkflowTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::first() ?? User::factory()->create([
            'role' => 'Admin',
            'status' => 'active',
        ]);
    }

    public function test_can_view_supply_inventory_page(): void
    {
        $firstSupply = Supply::orderBy('nama_barang')->first();

        $response = $this->actingAs($this->user)->get(route('supply.inventory.index'));
        $response->assertStatus(200);
        $response->assertSee('Stok Persediaan (Supply)');
        if ($firstSupply) {
            $response->assertSee($firstSupply->nama_barang);
        }
    }

    public function test_can_add_stock_to_inventory(): void
    {
        $supply = Supply::where('kode_barang', 'ATK-001')->firstOrFail();
        $initialStock = $supply->stok;

        $response = $this->actingAs($this->user)->post(route('supply.inventory.stok-masuk', $supply->id), [
            'jumlah' => 10,
            'tanggal' => now()->toDateString(),
            'keterangan' => 'Pengadaan stok tambahan',
        ]);

        $response->assertRedirect(route('supply.inventory.index'));
        $supply->refresh();
        $this->assertEquals($initialStock + 10, $supply->stok);
    }

    public function test_full_supply_request_distribution_and_bast_flow(): void
    {
        Storage::fake('public');

        $supply = Supply::where('kode_barang', 'ATK-001')->firstOrFail();
        $stockBefore = $supply->stok;
        $qtyDiminta = 5;

        // 1. Buat Permintaan Supply
        $postData = [
            'tanggal_permintaan' => now()->toDateString(),
            'unit_kerja' => 'Tim Nerwilis',
            'nama_pemohon' => 'Ahmad Pegawai, S.Tr.Stat',
            'nip_pemohon' => '199501012020121001',
            'jabatan_pemohon' => 'Statistisi Ahli Pertama',
            'keperluan' => 'Pencetakan bahan rilis PDRB Triwulan',
            'items' => [
                [
                    'supply_id' => $supply->id,
                    'jumlah' => $qtyDiminta,
                    'catatan' => 'Untuk kuesioner',
                ],
            ],
        ];

        $resStore = $this->actingAs($this->user)->post(route('supply.permintaan.store'), $postData);
        $resStore->assertRedirect();

        $permintaan = SupplyPermintaan::where('nama_pemohon', 'Ahmad Pegawai, S.Tr.Stat')->latest()->firstOrFail();
        $this->assertEquals('Menunggu', $permintaan->status);
        $this->assertCount(1, $permintaan->items);

        // 2. Proses Distribusi & Generate BAST (Stok berkurang otomatis)
        $permintaanItem = $permintaan->items->first();
        $distribusiData = [
            'tanggal_bast' => now()->toDateString(),
            'pihak_pertama_nama' => 'Budi Santoso',
            'pihak_pertama_nip' => '198501012010121002',
            'pihak_pertama_jabatan' => 'Pengelola BMN',
            'items' => [
                [
                    'id' => $permintaanItem->id,
                    'jumlah_disetujui' => $qtyDiminta,
                ],
            ],
        ];

        $resDistribusi = $this->actingAs($this->user)->post(route('supply.permintaan.distribusikan', $permintaan->id), $distribusiData);
        $resDistribusi->assertRedirect();

        // Verifikasi status permintaan & stok berkurang
        $permintaan->refresh();
        $supply->refresh();
        $this->assertEquals('Didistribusikan', $permintaan->status);
        $this->assertEquals($stockBefore - $qtyDiminta, $supply->stok);

        // Verifikasi BAST tergenerate otomatis
        $this->assertNotNull($permintaan->distribusi);
        $bast = $permintaan->distribusi;
        $this->assertEquals('Menunggu TTD', $bast->status);
        $this->assertCount(1, $bast->items);

        // 3. Akses Halaman Detail Distribusi
        $resShowBast = $this->actingAs($this->user)->get(route('supply.distribusi.show', $bast->id));
        $resShowBast->assertStatus(200);
        $resShowBast->assertSee($bast->nomor_bast);
        $resShowBast->assertSee('Ahmad Pegawai, S.Tr.Stat');
    }

    public function test_can_export_supply_inventory_to_csv(): void
    {
        $response = $this->actingAs($this->user)->get(route('supply.inventory.export'));
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename="stok_persediaan_supply_', $response->headers->get('Content-Disposition'));
    }

    public function test_supply_inventory_page_does_not_have_distribusikan_barang_button(): void
    {
        $response = $this->actingAs($this->user)->get(route('supply.inventory.index'));
        $response->assertStatus(200);
        $response->assertDontSee('Distribusikan Barang');
        $response->assertDontSee('Distribusikan / Keluarkan barang ini');
    }

    public function test_supply_inventory_page_does_not_have_import_data_button_or_modal(): void
    {
        $response = $this->actingAs($this->user)->get(route('supply.inventory.index'));
        $response->assertStatus(200);
        $response->assertDontSee('Import Data');
        $response->assertDontSee('showImportModal');
        $response->assertDontSee('closeImportModal');
    }

    public function test_supply_inventory_page_does_not_have_manual_masuk_action_button(): void
    {
        $response = $this->actingAs($this->user)->get(route('supply.inventory.index'));
        $response->assertStatus(200);
        $response->assertDontSee('>Masuk</span>', false);
        $response->assertDontSee('openStokMasukModal');
        $response->assertDontSee('showStokMasukModal');
    }

    public function test_supply_inventory_page_does_not_have_tambah_barang_baru_button(): void
    {
        $response = $this->actingAs($this->user)->get(route('supply.inventory.index'));
        $response->assertStatus(200);
        $response->assertDontSee('Tambah Barang Baru');
        $response->assertDontSee('openTambahModal');
    }

    public function test_can_distribute_directly_from_inventory_and_reduce_stock(): void
    {
        $supply = Supply::first();
        $stockBefore = $supply->stok;
        $qtyDistribusi = 3;

        // 1. Check create form with supply_id prefilled & pengelola inputs removed
        $resCreate = $this->actingAs($this->user)->get(route('supply.distribusi.create', ['supply_id' => $supply->id]));
        $resCreate->assertStatus(200);
        $resCreate->assertSee($supply->nama_barang);
        $resCreate->assertDontSee('name="pihak_pertama_nama"', false);
        $resCreate->assertDontSee('PENGELOLA');

        // 2. Post direct distribution (without submitting pihak_pertama inputs)
        $postData = [
            'tanggal_bast' => now()->toDateString(),
            'pihak_kedua_nama' => 'Staff Penerima Langsung',
            'pihak_kedua_unit_kerja' => 'Tim Nerwilis',
            'kegiatan_keperluan' => 'Operasional bulanan',
            'items' => [
                [
                    'supply_id' => $supply->id,
                    'nama_barang' => $supply->nama_barang,
                    'jumlah' => $qtyDistribusi,
                    'satuan' => $supply->satuan,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('supply.distribusi.store'), $postData);
        $response->assertRedirect();

        $supply->refresh();
        $this->assertEquals($stockBefore - $qtyDistribusi, $supply->stok);

        $bast = SupplyDistribusi::where('pihak_kedua_nama', 'Staff Penerima Langsung')->latest()->first();
        $this->assertNotNull($bast);
        $this->assertEquals('Menunggu TTD', $bast->status);
    }

    public function test_cannot_distribute_more_than_available_stock(): void
    {
        $supply = Supply::first();
        $overQty = $supply->stok + 100;

        $postData = [
            'tanggal_bast' => now()->toDateString(),
            'pihak_pertama_nama' => 'Petugas Gudang BPS',
            'pihak_pertama_jabatan' => 'Pengelola BMN',
            'pihak_kedua_nama' => 'Staff Pemohon Over',
            'pihak_kedua_unit_kerja' => 'Tim Produksi',
            'items' => [
                [
                    'supply_id' => $supply->id,
                    'nama_barang' => $supply->nama_barang,
                    'jumlah' => $overQty,
                    'satuan' => $supply->satuan,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('supply.distribusi.store'), $postData);
        $response->assertSessionHasErrors('items');
    }

    public function test_supply_realisasi_selesai_automatically_increases_supply_stock(): void
    {
        Storage::fake('public');

        $supply = Supply::first();
        $stockBefore = $supply->stok;
        $volumeRealized = 25;

        // 1. Buat Realisasi jenis Supply
        $realisasi = Realisasi::create([
            'no_fpa' => 'FPA-TEST/2026/01',
            'vendor' => 'CV Sumber Kertas',
            'pekerjaan' => 'Pengadaan '.$supply->nama_barang,
            'jenis' => 'Supply',
            'tanggal_pengajuan' => now()->toDateString(),
            'tanggal_mulai' => now()->toDateString(),
            'status' => 'siap realisasi',
            'total_realisasi' => 0,
        ]);

        $ra = $realisasi->realisasiAsets()->create([
            'nama_aset' => $supply->nama_barang,
            'kode_inventaris' => 'Supply / Persediaan',
            'subtotal' => 0,
        ]);

        $kakFile = UploadedFile::fake()->create('kak.pdf', 100, 'application/pdf');
        $kuitansiFile = UploadedFile::fake()->create('kuitansi.pdf', 100, 'application/pdf');

        $postData = [
            'tanggal_selesai' => now()->toDateString(),
            'kak' => $kakFile,
            'kuitansi' => $kuitansiFile,
            'asetList' => [
                [
                    'id' => $ra->id,
                    'nama' => $supply->nama_barang,
                    'items' => [
                        [
                            'uraian' => $supply->nama_barang,
                            'vol' => $volumeRealized,
                            'satuan' => $supply->satuan,
                            'hargaSatuan' => 50000,
                        ],
                    ],
                ],
            ],
        ];

        // 2. Submit Selesai
        $res = $this->actingAs($this->user)->post(route('realisasi.selesai', $realisasi->id), $postData);
        $res->assertRedirect(route('realisasi.index'));

        // 3. Verifikasi stok bertambah sesuai volume yang direalisasikan
        $supply->refresh();
        $realisasi->refresh();

        $this->assertEquals('Selesai', $realisasi->status);
        $this->assertTrue((bool) $realisasi->stok_masuk_processed);
        $this->assertEquals($stockBefore + $volumeRealized, $supply->stok);

        // Verifikasi mutasi tercatat
        $mutasi = SupplyMutasi::where('realisasi_id', $realisasi->id)->first();
        $this->assertNotNull($mutasi);
        $this->assertEquals('masuk', $mutasi->jenis);
        $this->assertEquals($volumeRealized, $mutasi->jumlah);
    }

    public function test_supply_distribusi_decreases_supply_stock_and_restores_on_destroy(): void
    {
        $supply = Supply::first();
        $stockBefore = $supply->stok;
        $qtyDistribusi = 7;

        $distribusiData = [
            'tanggal_bast' => now()->toDateString(),
            'pihak_pertama_nama' => 'Petugas BMN',
            'pihak_pertama_nip' => '198501012010121002',
            'pihak_pertama_jabatan' => 'Pengelola Persediaan',
            'pihak_kedua_nama' => 'Penerima BAST',
            'pihak_kedua_nip' => '199001012015031001',
            'pihak_kedua_jabatan' => 'Staf Teknis',
            'pihak_kedua_unit_kerja' => 'Tim Produksi',
            'kegiatan_keperluan' => 'Kebutuhan survei lapangan',
            'items' => [
                [
                    'supply_id' => $supply->id,
                    'nama_barang' => $supply->nama_barang,
                    'spesifikasi' => '',
                    'jumlah' => $qtyDistribusi,
                    'satuan' => $supply->satuan,
                    'keterangan' => 'Penyerahan barang',
                ],
            ],
        ];

        // 1. Simpan distribusi
        $res = $this->actingAs($this->user)->post(route('supply.distribusi.store'), $distribusiData);
        $res->assertRedirect();

        $supply->refresh();
        $this->assertEquals($stockBefore - $qtyDistribusi, $supply->stok);

        $distribusi = SupplyDistribusi::latest('id')->firstOrFail();
        $mutasi = SupplyMutasi::where('supply_distribusi_id', $distribusi->id)->first();
        $this->assertNotNull($mutasi);
        $this->assertEquals('keluar', $mutasi->jenis);
        $this->assertEquals($qtyDistribusi, $mutasi->jumlah);

        // 2. Hapus distribusi -> stok kembali
        $resDel = $this->actingAs($this->user)->delete(route('supply.distribusi.destroy', $distribusi->id));
        $resDel->assertRedirect(route('supply.distribusi.index'));

        $supply->refresh();
        $this->assertEquals($stockBefore, $supply->stok);
    }

    public function test_realisasi_index_filters_by_jenis(): void
    {
        $realisasiMaintenance = Realisasi::create([
            'no_fpa' => 'FPA-MT-999',
            'vendor' => 'CV Servis AC',
            'pekerjaan' => 'Pemeliharaan AC Ruang Rapat',
            'jenis' => 'Maintenance',
            'status' => 'siap realisasi',
        ]);

        $realisasiSupply = Realisasi::create([
            'no_fpa' => 'FPA-SP-999',
            'vendor' => 'Toko ATK Makmur',
            'pekerjaan' => 'Pengadaan Kertas HVS Kantor',
            'jenis' => 'Supply',
            'status' => 'siap realisasi',
        ]);

        // Filter Supply
        $resSupply = $this->actingAs($this->user)->get(route('realisasi.index', ['status' => 'siap realisasi', 'jenis' => 'Supply']));
        $resSupply->assertStatus(200);
        $resSupply->assertSee('Pengadaan Kertas HVS Kantor');
        $resSupply->assertDontSee('Pemeliharaan AC Ruang Rapat');

        // Filter Maintenance
        $resMaintenance = $this->actingAs($this->user)->get(route('realisasi.index', ['status' => 'siap realisasi', 'jenis' => 'Maintenance']));
        $resMaintenance->assertStatus(200);
        $resMaintenance->assertSee('Pemeliharaan AC Ruang Rapat');
        $resMaintenance->assertDontSee('Pengadaan Kertas HVS Kantor');
    }

    public function test_realisasi_supply_views_and_completion_with_supply_items(): void
    {
        Storage::fake('public');

        $supply = Supply::first();

        // 1. Create a Supply Realisasi in 'siap realisasi'
        $realisasi = Realisasi::create([
            'no_fpa' => 'FPA-SUP-TEST',
            'vendor' => 'Toko Alat Tulis',
            'pekerjaan' => 'Pengadaan Barang Persediaan Kantor',
            'jenis' => 'Supply',
            'tanggal_pengajuan' => now()->toDateString(),
            'tanggal_mulai' => now()->toDateString(),
            'status' => 'siap realisasi',
            'total_realisasi' => 0,
        ]);

        // Proses view check
        $resProses = $this->actingAs($this->user)->get(route('realisasi.proses', $realisasi->id));
        $resProses->assertStatus(200);
        $resProses->assertSee('Input Realisasi Supply (Persediaan)');
        $resProses->assertSee('Rincian Barang Persediaan Realisasi');
        $resProses->assertSee('+ Tambah Barang Persediaan');
        $resProses->assertDontSee('Laptop Dell Latitude');

        // Selesai submit using supplyItems
        $kak = UploadedFile::fake()->create('kak.pdf', 100, 'application/pdf');
        $kuitansi = UploadedFile::fake()->create('kuitansi.pdf', 100, 'application/pdf');

        $resSelesai = $this->actingAs($this->user)->post(route('realisasi.selesai', $realisasi->id), [
            'tanggal_selesai' => now()->toDateString(),
            'kak' => $kak,
            'kuitansi' => $kuitansi,
            'supplyItems' => [
                [
                    'uraian' => $supply->nama_barang,
                    'vol' => 15,
                    'satuan' => $supply->satuan,
                    'hargaSatuan' => 45000,
                ],
            ],
        ]);
        $resSelesai->assertRedirect(route('realisasi.index'));

        $realisasi->refresh();
        $this->assertEquals('Selesai', $realisasi->status);
        $this->assertEquals(675000, $realisasi->total_realisasi);

        // Show view check
        $resShow = $this->actingAs($this->user)->get(route('realisasi.show', $realisasi->id));
        $resShow->assertStatus(200);
        $resShow->assertSee('Detail Realisasi Supply (Persediaan)');
        $resShow->assertSee('Rincian Realisasi Barang Persediaan');
        $resShow->assertSee($supply->nama_barang);
        $resShow->assertDontSee('Biaya Realisasi per Aset');
        $resShow->assertDontSee('Aset 1:');

        // Edit view check
        $resEdit = $this->actingAs($this->user)->get(route('realisasi.edit', $realisasi->id));
        $resEdit->assertStatus(200);
        $resEdit->assertSee('Edit Realisasi Supply (Persediaan)');
        $resEdit->assertSee('Rincian Barang Persediaan Realisasi');
        $resEdit->assertSee('+ Tambah Barang Persediaan');
        $resEdit->assertDontSee('Biaya Realisasi per Aset');

        // Update submit
        $resUpdate = $this->actingAs($this->user)->put(route('realisasi.update', $realisasi->id), [
            'no_fpa' => 'FPA-SUP-TEST-REV',
            'pekerjaan' => 'Pengadaan Barang Persediaan Kantor (Revisi)',
            'vendor' => 'Toko Alat Tulis Sejahtera',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->toDateString(),
            'supplyItems' => [
                [
                    'uraian' => $supply->nama_barang,
                    'vol' => 20,
                    'satuan' => $supply->satuan,
                    'hargaSatuan' => 50000,
                ],
            ],
        ]);
        $resUpdate->assertRedirect(route('realisasi.index'));

        $realisasi->refresh();
        $this->assertEquals('FPA-SUP-TEST-REV', $realisasi->no_fpa);
        $this->assertEquals(1000000, $realisasi->total_realisasi);
    }

    public function test_supply_proses_modal_inputs_carry_over_to_realisasi_form_and_persist_for_supply_only(): void
    {
        Storage::fake('public');

        $supply = Supply::first();

        // 1. Buat Permintaan Supply
        $permintaanSupply = Permintaan::create([
            'pekerjaan' => 'Pengadaan Kebutuhan ATK Kantor',
            'jenis' => 'Supply',
            'tanggal' => now()->toDateString(),
            'status' => 'Diajukan',
        ]);

        $paSupply = $permintaanSupply->permintaanAsets()->create([
            'aset_id' => null,
            'nama_barang' => $supply->nama_barang,
            'deskripsi_kerusakan' => 'Kebutuhan persediaan',
        ]);

        $paSupply->rabItems()->create([
            'uraian' => $supply->nama_barang,
            'volume' => 10,
            'satuan' => $supply->satuan,
            'harga_satuan' => 25000,
            'jumlah' => 250000,
        ]);

        // 2. Submit modal proses permintaan dengan 3 input (no_fpa, vendor, tanggal_mulai)
        $modalData = [
            'no_fpa' => 'FPA-SUPPLY-MODAL-001',
            'vendor' => 'CV ATK Makmur Nusantara',
            'tanggal_mulai' => now()->toDateString(),
        ];

        $resProsesModal = $this->actingAs($this->user)->post(route('permintaan.proses', $permintaanSupply->id), $modalData);
        $realisasiSupply = Realisasi::where('permintaan_id', $permintaanSupply->id)->first();
        $this->assertNotNull($realisasiSupply);
        $resProsesModal->assertRedirect(route('realisasi.proses', $realisasiSupply->id));

        // 3. Verifikasi halaman realisasi.proses untuk Supply merender inputan (editable) dan terbawa dari modal
        $resPageSupply = $this->actingAs($this->user)->get(route('realisasi.proses', $realisasiSupply->id));
        $resPageSupply->assertStatus(200);
        $resPageSupply->assertSee('name="no_fpa"', false);
        $resPageSupply->assertSee('name="vendor"', false);
        $resPageSupply->assertSee('name="tanggal_mulai"', false);
        $resPageSupply->assertSee('Toko / Penyedia Barang');
        $resPageSupply->assertSee('Tanggal Pembelian / Pemesanan');
        $resPageSupply->assertSee('Tanggal Penerimaan Barang');
        $resPageSupply->assertSee('Terbawa dari modal proses');
        $resPageSupply->assertSee('FPA-SUPPLY-MODAL-001');
        $resPageSupply->assertSee('CV ATK Makmur Nusantara');

        // 4. Verifikasi bahwa untuk Maintenance, input tetap read-only span dan tidak ada input editable no_fpa
        $permintaanMaint = Permintaan::create([
            'pekerjaan' => 'Perbaikan Komputer Staff',
            'jenis' => 'Maintenance',
            'tanggal' => now()->toDateString(),
            'status' => 'Diajukan',
        ]);
        $realisasiMaint = Realisasi::create([
            'permintaan_id' => $permintaanMaint->id,
            'no_fpa' => 'FPA-MAINT-READONLY-002',
            'vendor' => 'PT Servis Komputer',
            'pekerjaan' => $permintaanMaint->pekerjaan,
            'jenis' => 'Maintenance',
            'tanggal_pengajuan' => now()->toDateString(),
            'tanggal_mulai' => now()->toDateString(),
            'status' => 'siap realisasi',
            'total_realisasi' => 0,
        ]);
        $resPageMaint = $this->actingAs($this->user)->get(route('realisasi.proses', $realisasiMaint->id));
        $resPageMaint->assertStatus(200);
        $resPageMaint->assertSee('Dari FPA awal (Read-only)');
        $resPageMaint->assertSee('FPA-MAINT-READONLY-002');
        $resPageMaint->assertSee('PT Servis Komputer');

        // 5. Submit selesai untuk Supply dengan nilai revisi dari form
        $kak = UploadedFile::fake()->create('kak.pdf', 100, 'application/pdf');
        $kuitansi = UploadedFile::fake()->create('kuitansi.pdf', 100, 'application/pdf');
        $raSupply = $realisasiSupply->realisasiAsets()->first();

        $resSelesaiSupply = $this->actingAs($this->user)->post(route('realisasi.selesai', $realisasiSupply->id), [
            'no_fpa' => 'FPA-SUPPLY-MODAL-REVISED',
            'vendor' => 'CV ATK Makmur Nusantara (Final)',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->toDateString(),
            'kak' => $kak,
            'kuitansi' => $kuitansi,
            'supplyItems' => [
                [
                    'uraian' => $supply->nama_barang,
                    'vol' => 10,
                    'satuan' => $supply->satuan,
                    'hargaSatuan' => 25000,
                ],
            ],
        ]);

        $resSelesaiSupply->assertRedirect(route('realisasi.index'));

        $realisasiSupply->refresh();
        $this->assertEquals('FPA-SUPPLY-MODAL-REVISED', $realisasiSupply->no_fpa);
        $this->assertEquals('CV ATK Makmur Nusantara (Final)', $realisasiSupply->vendor);
        $this->assertEquals('Selesai', $realisasiSupply->status);
    }

    public function test_detail_rab_permintaan_is_populated_and_collapsible_in_maintenance_proses_page(): void
    {
        $realisasi = Realisasi::create([
            'no_fpa' => 'FPA-MT-TEST-RAB',
            'vendor' => 'CV Servis AC',
            'pekerjaan' => 'Pemeliharaan AC Ruang Rapat',
            'jenis' => 'Maintenance',
            'status' => 'siap realisasi',
            'total_realisasi' => 0,
        ]);

        $response = $this->actingAs($this->user)->get(route('realisasi.proses', $realisasi->id));
        $response->assertStatus(200);

        // Memastikan judul card RAB dan teks buka/tutup ada
        $response->assertSee('Detail RAB Permintaan');
        $response->assertSee('buka / tutup');
        $response->assertSee('openRab');

        // Memastikan isi item RAB terisi (fallback default atau dari permintaan)
        $response->assertSee('Laptop Dell Latitude 5420');
        $response->assertSee('Penggantian Baterai Original');
        $response->assertSee('Jasa Pemasangan');
        $response->assertSee('1.400.000');
    }

    public function test_detail_rab_permintaan_is_collapsible_in_supply_proses_page(): void
    {
        $supply = Supply::first();
        $permintaan = Permintaan::create([
            'pekerjaan' => 'Pengadaan Kertas HVS',
            'jenis' => 'Supply',
            'tanggal' => now()->toDateString(),
            'status' => 'Disetujui',
        ]);
        $pa = $permintaan->permintaanAsets()->create([
            'nama_barang' => $supply->nama_barang,
            'kode_inventaris' => 'Supply / Persediaan',
            'total_anggaran' => 500000,
        ]);
        $pa->rabItems()->create([
            'uraian' => 'Kertas HVS A4 80gr',
            'volume' => 10,
            'satuan' => 'rim',
            'harga_satuan' => 50000,
            'jumlah' => 500000,
        ]);

        $realisasi = Realisasi::create([
            'permintaan_id' => $permintaan->id,
            'no_fpa' => 'FPA-SUP-TEST-RAB',
            'vendor' => 'Toko ATK Sejahtera',
            'pekerjaan' => 'Pengadaan Kertas HVS',
            'jenis' => 'Supply',
            'status' => 'siap realisasi',
            'total_realisasi' => 0,
        ]);

        $response = $this->actingAs($this->user)->get(route('realisasi.proses', $realisasi->id));
        $response->assertStatus(200);

        // Memastikan header card RAB Supply buka/tutup ada
        $response->assertSee('Detail RAB Permintaan Supply');
        $response->assertSee('buka / tutup');
        $response->assertSee('openRabSupply');
        $response->assertSee('Kertas HVS A4 80gr');
        $response->assertSee('500.000');
    }

    public function test_standalone_bast_menu_and_feature_are_removed_and_handled_via_realisasi_supply(): void
    {
        // 1. Sidebar dan Halaman Distribusi Barang tidak lagi memuat menu Berita Acara (BAST)
        $resDistribusi = $this->actingAs($this->user)->get(route('supply.distribusi.index'));
        $resDistribusi->assertStatus(200);
        $resDistribusi->assertSee('Distribusi Barang Persediaan');
        $resDistribusi->assertSee('Distribusi');
        $resDistribusi->assertDontSee('Berita Acara (BAST)');

        // 2. Route standalone BAST sudah dihapus
        $this->assertFalse(Route::has('supply.bast.index'));

        // 3. Dashboard tidak memuat link ke standalone BAST
        $resDashboard = $this->actingAs($this->user)->get(route('dashboard'));
        $resDashboard->assertStatus(200);
        $resDashboard->assertDontSee('Dokumen BAST Terbit');
        $resDashboard->assertSee('Distribusi Barang');
        $resDashboard->assertSee('Realisasi Supply');
    }

    public function test_bast_generator_and_pdf_download(): void
    {
        $permintaan = Permintaan::create([
            'pekerjaan' => 'Pengadaan Kertas HVS BAST',
            'jenis' => 'Supply',
            'status' => 'Disetujui',
            'tanggal' => now()->toDateString(),
        ]);

        // Preview BAST generator page
        $resPreview = $this->actingAs($this->user)->get(route('permintaan.bast', $permintaan->id));
        $resPreview->assertStatus(200);
        $resPreview->assertSee('Berita Acara Serah Terima (BAST)');
        $resPreview->assertSee('Unduh Dokumen BAST (PDF)');
        $resPreview->assertSee('Sesuaikan Data');

        // Unduh PDF BAST
        $resPdf = $this->actingAs($this->user)->get(route('permintaan.bast.pdf', $permintaan->id));
        $resPdf->assertStatus(200);
        $this->assertEquals('application/pdf', $resPdf->headers->get('content-type'));
    }

    public function test_realisasi_upload_bast_document_and_show(): void
    {
        Storage::fake('public');

        $permintaan = Permintaan::create([
            'pekerjaan' => 'Pengadaan Tinta Printer BAST',
            'jenis' => 'Supply',
            'status' => 'Disetujui',
            'tanggal' => now()->toDateString(),
        ]);

        $realisasi = Realisasi::create([
            'permintaan_id' => $permintaan->id,
            'no_fpa' => 'FPA-BAST-REAL-01',
            'vendor' => 'Toko ATK Sejahtera',
            'pekerjaan' => 'Pengadaan Tinta Printer BAST',
            'jenis' => 'Supply',
            'status' => 'siap realisasi',
            'total_realisasi' => 0,
            'kak_file' => 'kak_dokumen.pdf',
        ]);

        $file = UploadedFile::fake()->create('dokumen_bast.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->user)->post(route('realisasi.selesai', $realisasi->id), [
            'vendor' => 'Toko ATK Sejahtera',
            'total_realisasi' => 300000,
            'bast' => $file,
            'tanggal_selesai' => now()->toDateString(),
            'supplyItems' => [
                [
                    'uraian' => 'Tinta Printer',
                    'vol' => 1,
                    'satuan' => 'botol',
                    'hargaSatuan' => 300000,
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $realisasi->refresh();
        $this->assertEquals('Selesai', $realisasi->status);
        $this->assertNotNull($realisasi->bast_file);
        Storage::disk('public')->assertExists($realisasi->bast_file);

        // View show page
        $resShow = $this->actingAs($this->user)->get(route('realisasi.show', $realisasi->id));
        $resShow->assertStatus(200);
        $resShow->assertSee('Dokumen BAST');
    }

    public function test_distribusi_create_shows_all_inventory_supplies_including_out_of_stock_with_zero_stock_indicator(): void
    {
        // Pastikan ada barang dengan stok habis (0)
        $emptySupply = Supply::where('stok', '<=', 0)->first();
        if (! $emptySupply) {
            $emptySupply = Supply::create([
                'satker_id' => $this->user->satker_id ?? 1,
                'kode_barang' => 'EMPTY-TEST',
                'nama_barang' => 'Barang Stok Habis Uji',
                'kategori' => 'Alat Tulis Kantor (ATK)',
                'satuan' => 'kotak',
                'stok' => 0,
                'stok_minimum' => 5,
            ]);
        }

        $response = $this->actingAs($this->user)->get(route('supply.distribusi.create'));
        $response->assertStatus(200);

        // Memastikan dropdown katalog persediaan memuat nama barang stok habis dan indikator merah sisa stok = 0
        $response->assertSee($emptySupply->nama_barang);
        $response->assertSee('(sisa stok = 0)');
        $response->assertSee('text-rose-600');
    }

    public function test_supply_distribusi_bast_workflow_pdf_print_upload_and_delete(): void
    {
        Storage::fake('public');

        $supply = Supply::create([
            'satker_id' => $this->user->satker_id ?? 1,
            'kode_barang' => 'ATK-BAST-'.uniqid(),
            'nama_barang' => 'Kertas F4 BAST Test',
            'kategori' => 'Alat Tulis Kantor (ATK)',
            'satuan' => 'rim',
            'stok' => 20,
            'stok_minimum' => 2,
        ]);

        $distribusi = SupplyDistribusi::create([
            'satker_id' => $this->user->satker_id ?? 1,
            'nomor_bast' => SupplyDistribusi::generateNomorBast(),
            'tanggal_bast' => '2026-11-11',
            'pihak_pertama_nama' => 'Petugas BMN',
            'pihak_pertama_jabatan' => 'Pengelola BMN',
            'pihak_kedua_nama' => 'Penerima Barang Test',
            'pihak_kedua_unit_kerja' => 'Tim Statistik Sosial',
            'kegiatan_keperluan' => 'Survei Sosial Ekonomi',
            'status' => 'Menunggu TTD',
            'user_id' => $this->user->id,
        ]);

        $distribusi->items()->create([
            'supply_id' => $supply->id,
            'nama_barang' => $supply->nama_barang,
            'jumlah' => 2,
            'satuan' => 'rim',
            'keterangan' => 'Kondisi Baik',
        ]);

        // 1. Index shows action buttons & BAST status
        $resIndex = $this->actingAs($this->user)->get(route('supply.distribusi.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee($distribusi->nomor_bast);
        $resIndex->assertSee('Menunggu TTD');
        $resIndex->assertSee('Lihat');
        $resIndex->assertSee('PDF');

        // 2. Show page displays BAST details, print and export pdf buttons
        $resShow = $this->actingAs($this->user)->get(route('supply.distribusi.show', $distribusi->id));
        $resShow->assertStatus(200);
        $resShow->assertSee('Berita Acara Serah Terima');
        $resShow->assertSee('Sesuaikan Data');
        $resShow->assertSee('Cetak BAST');
        $resShow->assertSee('Ekspor PDF');
        $resShow->assertSee('Unggah Scan BAST');

        // 3. Print page
        $resPrint = $this->actingAs($this->user)->get(route('supply.distribusi.print', $distribusi->id));
        $resPrint->assertStatus(200);
        $resPrint->assertSee($distribusi->nomor_bast);

        // 4. Export PDF
        $resPdf = $this->actingAs($this->user)->get(route('supply.distribusi.pdf', [$distribusi->id, 'download' => 1]));
        $resPdf->assertStatus(200);
        $this->assertEquals('application/pdf', $resPdf->headers->get('Content-Type'));

        // 5. Upload signed BAST
        $file = UploadedFile::fake()->create('bast_bertandatangan.pdf', 500, 'application/pdf');
        $resUpload = $this->actingAs($this->user)->post(route('supply.distribusi.upload-ttd', $distribusi->id), [
            'file_bast_ttd' => $file,
            'catatan' => 'Dokumen asli telah ditandatangani basah.',
        ]);
        $resUpload->assertSessionHas('success');

        $distribusi->refresh();
        $this->assertEquals('Selesai', $distribusi->status);
        $this->assertNotNull($distribusi->file_bast_ttd);
        Storage::disk('public')->assertExists($distribusi->file_bast_ttd);

        // 6. Index now shows "Sudah TTD" / "Scan"
        $resIndexAfter = $this->actingAs($this->user)->get(route('supply.distribusi.index'));
        $resIndexAfter->assertStatus(200);
        $resIndexAfter->assertSee('Sudah TTD');

        // 7. Delete uploaded scan returns status to Menunggu TTD
        $resDeleteTtd = $this->actingAs($this->user)->delete(route('supply.distribusi.delete-ttd', $distribusi->id));
        $resDeleteTtd->assertSessionHas('info');

        $distribusi->refresh();
        $this->assertEquals('Menunggu TTD', $distribusi->status);
        $this->assertNull($distribusi->file_bast_ttd);

        // 8. Edit / Penyesuaian BAST
        $resEdit = $this->actingAs($this->user)->get(route('supply.distribusi.edit', $distribusi->id));
        $resEdit->assertStatus(200);
        $resEdit->assertSee('Penyesuaian Data Dokumen BAST');

        $newNomor = 'BAST-EDIT-'.uniqid();
        $resUpdate = $this->actingAs($this->user)->put(route('supply.distribusi.update', $distribusi->id), [
            'nomor_bast' => $newNomor,
            'tanggal_bast' => '2026-11-12',
            'pihak_pertama_nama' => 'Petugas BMN Diedit',
            'pihak_pertama_jabatan' => 'Kasubag Umum',
            'pihak_kedua_nama' => 'Penerima Barang Baru',
            'pihak_kedua_unit_kerja' => 'Tim IPDS',
            'kegiatan_keperluan' => 'Kebutuhan Survei Khusus',
        ]);
        $resUpdate->assertRedirect(route('supply.distribusi.show', $distribusi->id));
        $resUpdate->assertSessionHas('success');

        $distribusi->refresh();
        $this->assertEquals($newNomor, $distribusi->nomor_bast);
        $this->assertEquals('Penerima Barang Baru', $distribusi->pihak_kedua_nama);
        $this->assertEquals('Tim IPDS', $distribusi->pihak_kedua_unit_kerja);
    }
}
