<?php

namespace Tests\Feature;

use App\Models\Aset;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AsetTest extends TestCase
{
    public function test_can_view_aset_index_page(): void
    {
        $response = $this->get(route('aset.index'));
        $response->assertStatus(200);
        $response->assertSee('Daftar Aset & Inventaris BPS', false);
        $response->assertSee('TOTAL ASET TERDAFTAR');
        $response->assertSee('KONDISI BAIK');
        $response->assertSee('RUSAK RINGAN');
        $response->assertSee('RUSAK BERAT / NON-AKTIF');
        $response->assertSee('Tambah Aset Manual');
    }

    public function test_can_filter_aset_by_kondisi(): void
    {
        $response = $this->get(route('aset.index', ['kondisi' => 'Rusak Berat']));
        $response->assertStatus(200);
        $response->assertSee('Filter Aktif:');
        $response->assertSee('Kondisi: Rusak Berat');
    }

    public function test_can_store_aset_manually(): void
    {
        $uniqueNup = 'TEST_'.time();
        $payload = [
            'kode_barang' => '3050104999',
            'nup' => $uniqueNup,
            'nama_barang' => 'Laptop ThinkPad Pengujian',
            'merk' => 'Lenovo',
            'tipe' => 'ThinkPad T14 Gen 3',
            'kondisi' => 'Baik',
            'jenis_bmn' => 'Peralatan dan Mesin',
            'lokasi_ruang' => 'Ruang Integrasi Pengolahan',
            'pengguna' => 'Budi Santoso',
            'tanggal_perolehan' => '2023-01-15',
            'umur_aset' => 3,
            'nilai_perolehan' => '18500000',
            'nilai_buku' => '12000000',
            'nilai_penyusutan' => '6500000',
            'status_sbsn' => 'Non SBSN',
            'status_bmn_idle' => 'Tidak',
        ];

        $response = $this->post(route('aset.store'), $payload);
        $response->assertRedirect(route('aset.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('asets', [
            'kode_barang' => '3050104999',
            'nup' => $uniqueNup,
            'nama_barang' => 'Laptop ThinkPad Pengujian',
            'kondisi' => 'Baik',
            'lokasi_ruang' => 'Ruang Integrasi Pengolahan',
        ]);
    }

    public function test_cannot_store_duplicate_aset(): void
    {
        $uniqueNup = 'DUP_'.time();
        Aset::create([
            'kode_barang' => '3050104888',
            'nup' => $uniqueNup,
            'nama_barang' => 'Existing Asset',
            'kondisi' => 'Baik',
        ]);

        $payload = [
            'kode_barang' => '3050104888',
            'nup' => $uniqueNup,
            'nama_barang' => 'Duplicate Asset Attempt',
            'kondisi' => 'Rusak Ringan',
        ];

        $response = $this->post(route('aset.store'), $payload);
        $response->assertSessionHasErrors(['nup']);
    }

    public function test_can_update_aset_manually(): void
    {
        $uniqueNup = 'UPD_'.time();
        $aset = Aset::create([
            'kode_barang' => '3050104777',
            'nup' => $uniqueNup,
            'nama_barang' => 'Old Monitor 24 Inch',
            'merk' => 'Samsung',
            'tipe' => 'S24R350',
            'kondisi' => 'Baik',
            'lokasi_ruang' => 'Ruang TU',
        ]);

        $updatePayload = [
            'kode_barang' => '3050104777',
            'nup' => $uniqueNup,
            'nama_barang' => 'Updated Monitor 27 Inch UHD',
            'merk' => 'Dell',
            'tipe' => 'U2723QE',
            'kondisi' => 'Rusak Ringan',
            'lokasi_ruang' => 'Ruang Server & Jaringan',
            'pengguna' => 'Ahmad Teknisi',
            'tanggal_perolehan' => '2024-05-10',
            'nilai_perolehan' => '8500000',
        ];

        $response = $this->put(route('aset.update', $aset->id), $updatePayload);
        $response->assertRedirect(route('aset.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('asets', [
            'id' => $aset->id,
            'nama_barang' => 'Updated Monitor 27 Inch UHD',
            'merk' => 'Dell',
            'kondisi' => 'Rusak Ringan',
            'lokasi_ruang' => 'Ruang Server & Jaringan',
        ]);
    }

    public function test_can_view_aset_detail_json(): void
    {
        $aset = Aset::first();
        if ($aset) {
            $response = $this->get(route('aset.show', $aset->id));
            $response->assertStatus(200);
            $response->assertJsonStructure([
                'id',
                'kode_barang',
                'nup',
                'nama_barang',
                'kondisi',
                'can_maintenance',
                'raw_kode_barang',
                'raw_nup',
                'raw_nama_barang',
                'raw_kondisi',
            ]);
        }
    }

    public function test_can_export_aset_csv(): void
    {
        $response = $this->get(route('aset.export'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_can_import_aset_with_long_nama_and_description(): void
    {
        $longNama = str_repeat('Nama Pemegang Sertifikat Yang Sangat Panjang Sekali ', 10);
        $csvContent = "Kode Barang,NUP,Nama Barang,Kondisi,Nama\n3050109999,999,Monitor LED 4K,Baik,\"{$longNama}\"\n";
        $file = UploadedFile::fake()->createWithContent('test-aset.csv', $csvContent);

        $response = $this->post(route('aset.import'), [
            'file' => $file,
            'mode' => 'update',
        ]);

        $response->assertRedirect(route('aset.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('asets', [
            'kode_barang' => '3050109999',
            'nup' => '999',
            'nama' => $longNama,
        ]);
    }

    public function test_safe_update_mode_preserves_manually_surveyed_fields(): void
    {
        $uniqueNup = 'SAFE_'.time();
        $aset = Aset::create([
            'kode_barang' => '3050101111',
            'nup' => $uniqueNup,
            'nama_barang' => 'Laptop Survei Lapangan',
            'kondisi' => 'Rusak Ringan',
            'lokasi_ruang' => 'Ruang Integrasi Pengolahan',
            'pengguna' => 'Budi Santoso',
            'nilai_buku' => 10000000,
        ]);

        // Upload CSV from old SIMAN with different condition, location, user, but updated nilai_buku
        $csvContent = "Kode Barang,NUP,Nama Barang,Kondisi,Ruang,Pengguna,Nilai Buku\n3050101111,{$uniqueNup},Laptop Survei Lapangan,Baik,Gudang Arsip,Pegawai Lain,7500000\n";
        $file = UploadedFile::fake()->createWithContent('old_siman.csv', $csvContent);

        $response = $this->post(route('aset.import'), [
            'file' => $file,
            'mode' => 'safe_update',
            'ignore_blank' => '1',
        ]);

        $response->assertRedirect(route('aset.index'));
        $response->assertSessionHas('success');

        $aset->refresh();
        // Surveyed fields must be PROTECTED and NOT overwritten
        $this->assertEquals('Rusak Ringan', $aset->kondisi);
        $this->assertEquals('Ruang Integrasi Pengolahan', $aset->lokasi_ruang);
        $this->assertEquals('Budi Santoso', $aset->pengguna);

        // Financial/master field MUST be updated
        $this->assertEquals(7500000, (float) $aset->nilai_buku);
    }

    public function test_ignore_blank_cells_prevents_erasing_existing_database_values(): void
    {
        $uniqueNup = 'BLANK_'.time();
        $aset = Aset::create([
            'kode_barang' => '3050102222',
            'nup' => $uniqueNup,
            'nama_barang' => 'Printer Kasir',
            'merk' => 'Epson',
            'tipe' => 'TM-T82',
            'pengguna' => 'Kasir Utama',
        ]);

        // CSV where merk and pengguna are blank
        $csvContent = "Kode Barang,NUP,Nama Barang,Merk,Pengguna\n3050102222,{$uniqueNup},Printer Kasir,,\n";
        $file = UploadedFile::fake()->createWithContent('blank_cols.csv', $csvContent);

        $response = $this->post(route('aset.import'), [
            'file' => $file,
            'mode' => 'safe_update',
            'ignore_blank' => '1',
        ]);

        $response->assertRedirect(route('aset.index'));

        $aset->refresh();
        $this->assertEquals('Epson', $aset->merk);
        $this->assertEquals('Kasir Utama', $aset->pengguna);
    }

    public function test_skip_mode_does_not_modify_existing_records(): void
    {
        $uniqueNup = 'SKIP_'.time();
        $aset = Aset::create([
            'kode_barang' => '3050103333',
            'nup' => $uniqueNup,
            'nama_barang' => 'Scanner Dokumen',
            'kondisi' => 'Baik',
            'nilai_buku' => 2000000,
        ]);

        $csvContent = "Kode Barang,NUP,Nama Barang,Kondisi,Nilai Buku\n3050103333,{$uniqueNup},Scanner Dokumen,Rusak Berat,99999999\n";
        $file = UploadedFile::fake()->createWithContent('skip_test.csv', $csvContent);

        $response = $this->post(route('aset.import'), [
            'file' => $file,
            'mode' => 'skip',
        ]);

        $response->assertRedirect(route('aset.index'));

        $aset->refresh();
        $this->assertEquals('Baik', $aset->kondisi);
        $this->assertEquals(2000000, (float) $aset->nilai_buku);
    }

    public function test_rollback_import_restores_assets_to_pre_import_state(): void
    {
        $uniqueNup = 'ROLL_'.time();
        $aset = Aset::create([
            'kode_barang' => '3050104444',
            'nup' => $uniqueNup,
            'nama_barang' => 'Komputer All-in-One',
            'kondisi' => 'Rusak Ringan',
            'lokasi_ruang' => 'Ruang Server',
        ]);

        // Perform an overwrite import
        $csvContent = "Kode Barang,NUP,Nama Barang,Kondisi,Ruang\n3050104444,{$uniqueNup},Komputer All-in-One,Baik,Gudang\n";
        $file = UploadedFile::fake()->createWithContent('wrong_import.csv', $csvContent);

        $this->post(route('aset.import'), [
            'file' => $file,
            'mode' => 'overwrite_all',
        ]);

        $aset->refresh();
        $this->assertEquals('Baik', $aset->kondisi);
        $this->assertEquals('Gudang', $aset->lokasi_ruang);

        // Perform rollback
        $rollbackResponse = $this->post(route('aset.rollback-import'));
        $rollbackResponse->assertRedirect(route('aset.index'));
        $rollbackResponse->assertSessionHas('success');

        $aset->refresh();
        $this->assertEquals('Rusak Ringan', $aset->kondisi);
        $this->assertEquals('Ruang Server', $aset->lokasi_ruang);
    }
}
