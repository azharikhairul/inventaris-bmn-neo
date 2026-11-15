<?php

namespace Tests\Feature;

use App\Models\Permintaan;
use App\Models\Realisasi;
use App\Models\Satker;
use App\Models\Supply;
use App\Models\User;
use App\Services\SatkerService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MultiSatkerTest extends TestCase
{
    use DatabaseTransactions;

    protected Satker $satkerProvinsi;

    protected Satker $satkerBoalemo;

    protected Satker $satkerPohuwato;

    protected User $superAdmin;

    protected User $adminProvinsi;

    protected User $userBoalemo;

    protected User $userPohuwato;

    protected function setUp(): void
    {
        parent::setUp();

        // Retrieve existing seeded satkers
        $this->satkerProvinsi = Satker::where('tingkat', 'Provinsi')->firstOrFail();
        $this->satkerBoalemo = Satker::where('nama_satker', 'like', '%Boalemo%')->firstOrFail();
        $this->satkerPohuwato = Satker::where('nama_satker', 'like', '%Pohuwato%')->firstOrFail();

        // Create test users
        $this->superAdmin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator Utama',
                'role' => 'Admin',
                'status' => 'active',
                'satker_id' => $this->satkerProvinsi->id,
            ]
        );

        $this->adminProvinsi = User::create([
            'name' => 'Admin Provinsi Test',
            'username' => 'admin.prov.test',
            'email' => 'admin.prov.test@bps.go.id',
            'role' => 'Admin',
            'status' => 'active',
            'satker_id' => $this->satkerProvinsi->id,
        ]);

        $this->userBoalemo = User::create([
            'name' => 'Staff Boalemo Test',
            'username' => 'staff.boalemo.test',
            'email' => 'staff.boalemo.test@bps.go.id',
            'role' => 'Staff',
            'status' => 'active',
            'satker_id' => $this->satkerBoalemo->id,
        ]);

        $this->userPohuwato = User::create([
            'name' => 'Admin Pohuwato Test',
            'username' => 'admin.pohuwato.test',
            'email' => 'admin.pohuwato.test@bps.go.id',
            'role' => 'Admin',
            'status' => 'active',
            'satker_id' => $this->satkerPohuwato->id,
        ]);
    }

    public function test_super_admin_and_provincial_admin_can_access_all_satkers(): void
    {
        $this->assertTrue($this->superAdmin->canAccessAllSatkers());
        $this->assertTrue($this->adminProvinsi->canAccessAllSatkers());

        // Regency users cannot access all satkers
        $this->assertFalse($this->userBoalemo->canAccessAllSatkers());
        $this->assertFalse($this->userPohuwato->canAccessAllSatkers());
    }

    public function test_regency_user_cannot_switch_satker(): void
    {
        $response = $this->actingAs($this->userBoalemo)
            ->post(route('satker.switch'), [
                'satker_id' => $this->satkerPohuwato->id,
            ]);

        $response->assertSessionHas('error');
        $this->assertEquals($this->satkerBoalemo->id, SatkerService::getActiveSatkerId());
    }

    public function test_provincial_admin_can_switch_satker(): void
    {
        $response = $this->actingAs($this->adminProvinsi)
            ->post(route('satker.switch'), [
                'satker_id' => $this->satkerBoalemo->id,
            ]);

        $response->assertSessionHas('success');
        $response->assertSessionHas('active_satker_id', $this->satkerBoalemo->id);
    }

    public function test_regency_user_cannot_access_satker_management(): void
    {
        // Staff user gets redirected by AdminMiddleware
        $response = $this->actingAs($this->userBoalemo)
            ->get(route('satker.index'));

        $response->assertRedirect(route('profile.index'));

        // Regency admin gets 403 Forbidden from SatkerController
        $responseAdminKab = $this->actingAs($this->userPohuwato)
            ->get(route('satker.index'));

        $responseAdminKab->assertStatus(403);
    }

    public function test_provincial_admin_can_access_satker_management(): void
    {
        $response = $this->actingAs($this->adminProvinsi)
            ->get(route('satker.index'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen Satuan Kerja');
        $response->assertSee('BPS Kabupaten Boalemo');
    }

    public function test_permintaan_is_isolated_between_satkers(): void
    {
        // Create permintaan for Boalemo
        $permintaanBoalemo = Permintaan::create([
            'pekerjaan' => 'Pengadaan ATK Boalemo Khusus',
            'jenis' => 'Supply',
            'status' => 'Pending',
            'satker_id' => $this->satkerBoalemo->id,
            'keterangan' => 'Pengajuan Boalemo',
        ]);

        // Create permintaan for Pohuwato
        $permintaanPohuwato = Permintaan::create([
            'pekerjaan' => 'Pengadaan ATK Pohuwato Khusus',
            'jenis' => 'Supply',
            'status' => 'Pending',
            'satker_id' => $this->satkerPohuwato->id,
            'keterangan' => 'Pengajuan Pohuwato',
        ]);

        // When Boalemo user visits index, only Boalemo's permintaan is visible
        $responseBoalemo = $this->actingAs($this->userBoalemo)
            ->get(route('permintaan.index'));

        $responseBoalemo->assertStatus(200);
        $responseBoalemo->assertSee('Pengadaan ATK Boalemo Khusus');
        $responseBoalemo->assertDontSee('Pengadaan ATK Pohuwato Khusus');

        // When Pohuwato user visits index, only Pohuwato's permintaan is visible
        $responsePohuwato = $this->actingAs($this->userPohuwato)
            ->get(route('permintaan.index'));

        $responsePohuwato->assertStatus(200);
        $responsePohuwato->assertSee('Pengadaan ATK Pohuwato Khusus');
        $responsePohuwato->assertDontSee('Pengadaan ATK Boalemo Khusus');
    }

    public function test_supply_inventory_is_isolated_between_satkers(): void
    {
        $supplyBoalemo = Supply::create([
            'kode_barang' => 'SUP-BOA-01',
            'nama_barang' => 'Kertas F4 Boalemo Segel',
            'satuan' => 'Rim',
            'stok_tersedia' => 50,
            'satker_id' => $this->satkerBoalemo->id,
        ]);

        $supplyPohuwato = Supply::create([
            'kode_barang' => 'SUP-POH-01',
            'nama_barang' => 'Toner Printer Pohuwato Segel',
            'satuan' => 'Pcs',
            'stok_tersedia' => 10,
            'satker_id' => $this->satkerPohuwato->id,
        ]);

        // Boalemo user only sees Boalemo supply
        $responseBoalemo = $this->actingAs($this->userBoalemo)
            ->get(route('supply.inventory.index'));

        $responseBoalemo->assertStatus(200);
        $responseBoalemo->assertSee('Kertas F4 Boalemo Segel');
        $responseBoalemo->assertDontSee('Toner Printer Pohuwato Segel');

        // Pohuwato user only sees Pohuwato supply
        $responsePohuwato = $this->actingAs($this->userPohuwato)
            ->get(route('supply.inventory.index'));

        $responsePohuwato->assertStatus(200);
        $responsePohuwato->assertSee('Toner Printer Pohuwato Segel');
        $responsePohuwato->assertDontSee('Kertas F4 Boalemo Segel');
    }

    public function test_new_permintaan_by_regency_user_automatically_assigns_satker(): void
    {
        $response = $this->actingAs($this->userBoalemo)
            ->post(route('permintaan.store'), [
                'pekerjaan' => 'Permintaan Baru Otomatis Boalemo',
                'jenis' => 'Supply',
                'asetList' => [
                    [
                        'nama_barang' => 'Pulpen Gel Hitam',
                        'rabItems' => [
                            [
                                'uraian' => 'Pulpen Gel Hitam',
                                'volume' => 10,
                                'satuan' => 'buah',
                                'hargaSatuan' => 5000,
                            ],
                        ],
                    ],
                ],
            ]);

        $response->assertRedirect(route('permintaan.index'));

        $this->assertDatabaseHas('permintaans', [
            'pekerjaan' => 'Permintaan Baru Otomatis Boalemo',
            'satker_id' => $this->satkerBoalemo->id,
        ]);
    }

    public function test_all_application_pages_load_successfully_for_super_admin_and_regency_user(): void
    {
        $superAdminRoutes = [
            'dashboard',
            'aset.index',
            'permintaan.index',
            'realisasi.index',
            'kartu-kendali.index',
            'supply.inventory.index',
            'supply.permintaan.index',
            'supply.distribusi.index',
            'users.index',
            'satker.index',
            'profile.index',
        ];

        foreach ($superAdminRoutes as $routeName) {
            $response = $this->actingAs($this->superAdmin)->get(route($routeName));
            $this->assertEquals(200, $response->status(), "Route [{$routeName}] failed to load with 200 for Super Admin.");
        }

        $regencyRoutes = [
            'dashboard',
            'aset.index',
            'permintaan.index',
            'realisasi.index',
            'kartu-kendali.index',
            'supply.inventory.index',
            'supply.permintaan.index',
            'supply.distribusi.index',
            'profile.index',
        ];

        foreach ($regencyRoutes as $routeName) {
            $response = $this->actingAs($this->userBoalemo)->get(route($routeName));
            $this->assertEquals(200, $response->status(), "Route [{$routeName}] failed to load with 200 for Regency User.");
        }
    }

    public function test_superadmin_viewing_other_satker_is_in_read_only_mode(): void
    {
        // 1. Superadmin switches to Boalemo
        $this->actingAs($this->superAdmin)
            ->withSession(['active_satker_id' => $this->satkerBoalemo->id]);

        $this->assertTrue(SatkerService::isViewingOtherSatker());
        $this->assertFalse(SatkerService::canManageCurrentScope());

        // 2. Permintaan: create redirect, store rejected (403), index shows Read-Only badge
        $resCreatePermintaan = $this->actingAs($this->superAdmin)
            ->withSession(['active_satker_id' => $this->satkerBoalemo->id])
            ->get(route('permintaan.create'));
        $resCreatePermintaan->assertRedirect(route('permintaan.index'));

        $resStorePermintaan = $this->actingAs($this->superAdmin)
            ->withSession(['active_satker_id' => $this->satkerBoalemo->id])
            ->post(route('permintaan.store'), [
                'pekerjaan' => 'Tes Tambah Saat Read Only',
                'jenis' => 'Maintenance',
            ]);
        $resStorePermintaan->assertStatus(403);

        $resIndexPermintaan = $this->actingAs($this->superAdmin)
            ->withSession(['active_satker_id' => $this->satkerBoalemo->id])
            ->get(route('permintaan.index'));
        $resIndexPermintaan->assertStatus(200);
        $resIndexPermintaan->assertSee('Mode Lihat Saja (Penambahan Dinonaktifkan)');
        $resIndexPermintaan->assertDontSee('Permintaan Maintenance');

        // 3. Aset: store rejected (403), index hides Tambah Aset Manual button
        $resStoreAset = $this->actingAs($this->superAdmin)
            ->withSession(['active_satker_id' => $this->satkerBoalemo->id])
            ->post(route('aset.store'), [
                'kode_barang' => '3050104001',
                'nup' => '9999',
                'nama_barang' => 'Laptop Read Only Test',
            ]);
        $resStoreAset->assertStatus(403);

        $resIndexAset = $this->actingAs($this->superAdmin)
            ->withSession(['active_satker_id' => $this->satkerBoalemo->id])
            ->get(route('aset.index'));
        $resIndexAset->assertStatus(200);
        $resIndexAset->assertDontSee('Tambah Aset Manual');
        $resIndexAset->assertDontSee('Import Data');

        // 4. Stok Persediaan: store rejected (403), index hides Tambah Barang Baru
        $resStoreSupply = $this->actingAs($this->superAdmin)
            ->withSession(['active_satker_id' => $this->satkerBoalemo->id])
            ->post(route('supply.inventory.store'), [
                'nama_barang' => 'Barang Read Only Test',
                'kategori' => 'ATK',
                'satuan' => 'buah',
                'stok' => 10,
            ]);
        $resStoreSupply->assertStatus(403);

        $resIndexSupply = $this->actingAs($this->superAdmin)
            ->withSession(['active_satker_id' => $this->satkerBoalemo->id])
            ->get(route('supply.inventory.index'));
        $resIndexSupply->assertStatus(200);
        $resIndexSupply->assertDontSee('Tambah Barang Baru');

        // 5. Supply Permintaan: create redirect, store rejected (403)
        $resCreateSupplyP = $this->actingAs($this->superAdmin)
            ->withSession(['active_satker_id' => $this->satkerBoalemo->id])
            ->get(route('supply.permintaan.create'));
        $resCreateSupplyP->assertRedirect(route('supply.permintaan.index'));

        $resStoreSupplyP = $this->actingAs($this->superAdmin)
            ->withSession(['active_satker_id' => $this->satkerBoalemo->id])
            ->post(route('supply.permintaan.store'), [
                'tanggal_permintaan' => '2026-10-30',
                'unit_kerja' => 'Umum',
                'nama_pemohon' => 'Budi',
                'keperluan' => 'Operasional',
                'items' => [['supply_id' => 1, 'jumlah' => 1]],
            ]);
        $resStoreSupplyP->assertStatus(403);

        // 6. Realisasi: index hides PROSES REALISASI, proses & edit redirect, update & selesai rejected (403)
        $permintaanBoalemo = Permintaan::create([
            'satker_id' => $this->satkerBoalemo->id,
            'pekerjaan' => 'Perbaikan AC Boalemo',
            'jenis' => 'Maintenance',
            'status' => 'Pending',
            'user_id' => $this->userBoalemo->id,
        ]);

        $realisasiBoalemo = Realisasi::create([
            'satker_id' => $this->satkerBoalemo->id,
            'permintaan_id' => $permintaanBoalemo->id,
            'no_fpa' => 'FPA-BOALEMO-001',
            'vendor' => 'CV Sejahtera',
            'pekerjaan' => 'Perbaikan AC Boalemo',
            'jenis' => 'Maintenance',
            'status' => 'siap realisasi',
        ]);

        $resIndexRealisasi = $this->actingAs($this->superAdmin)
            ->withSession(['active_satker_id' => $this->satkerBoalemo->id])
            ->get(route('realisasi.index'));
        $resIndexRealisasi->assertStatus(200);
        $resIndexRealisasi->assertDontSee('PROSES REALISASI');

        $resProsesRealisasi = $this->actingAs($this->superAdmin)
            ->withSession(['active_satker_id' => $this->satkerBoalemo->id])
            ->get(route('realisasi.proses', $realisasiBoalemo->id));
        $resProsesRealisasi->assertRedirect(route('realisasi.index'));

        $resSelesaiRealisasi = $this->actingAs($this->superAdmin)
            ->withSession(['active_satker_id' => $this->satkerBoalemo->id])
            ->post(route('realisasi.selesai', $realisasiBoalemo->id), [
                'tanggal_selesai' => '2026-10-30',
            ]);
        $resSelesaiRealisasi->assertStatus(403);

        $resEditRealisasi = $this->actingAs($this->superAdmin)
            ->withSession(['active_satker_id' => $this->satkerBoalemo->id])
            ->get(route('realisasi.edit', $realisasiBoalemo->id));
        $resEditRealisasi->assertRedirect(route('realisasi.index'));

        $resUpdateRealisasi = $this->actingAs($this->superAdmin)
            ->withSession(['active_satker_id' => $this->satkerBoalemo->id])
            ->put(route('realisasi.update', $realisasiBoalemo->id), [
                'no_fpa' => 'FPA-001-MOD',
                'pekerjaan' => 'AC Boalemo',
                'vendor' => 'Vendor',
                'tanggal_mulai' => '2026-10-01',
                'tanggal_selesai' => '2026-10-02',
            ]);
        $resUpdateRealisasi->assertStatus(403);

        // 7. Supply Distribusi: create redirect, store rejected (403)
        $resCreateDistribusi = $this->actingAs($this->superAdmin)
            ->withSession(['active_satker_id' => $this->satkerBoalemo->id])
            ->get(route('supply.distribusi.create'));
        $resCreateDistribusi->assertRedirect(route('supply.distribusi.index'));

        $resStoreDistribusi = $this->actingAs($this->superAdmin)
            ->withSession(['active_satker_id' => $this->satkerBoalemo->id])
            ->post(route('supply.distribusi.store'), [
                'tanggal_bast' => '2026-10-30',
                'pihak_pertama_nama' => 'Staff',
                'pihak_pertama_jabatan' => 'Petugas',
                'pihak_kedua_nama' => 'Penerima',
                'pihak_kedua_unit_kerja' => 'Seksi',
                'items' => [['nama_barang' => 'Barang', 'jumlah' => 1, 'satuan' => 'buah']],
            ]);
        $resStoreDistribusi->assertStatus(403);

        // 8. Sidebar Navigation in Read-Only Mode: only displays Dashboard, Data Aset, Permintaan, Realisasi, Kartu Kendali, Stok Persediaan, Distribusi & BAST
        $resSidebarReadOnly = $this->actingAs($this->superAdmin)
            ->withSession(['active_satker_id' => $this->satkerBoalemo->id])
            ->get(route('dashboard'));
        $resSidebarReadOnly->assertStatus(200);
        $resSidebarReadOnly->assertSee('Dashboard');
        $resSidebarReadOnly->assertSee('Data Aset');
        $resSidebarReadOnly->assertSee('Permintaan');
        $resSidebarReadOnly->assertSee('Realisasi');
        $resSidebarReadOnly->assertSee('Kartu Kendali');
        $resSidebarReadOnly->assertSee('Stok Persediaan');
        $resSidebarReadOnly->assertSee('Distribusi Barang');
        $resSidebarReadOnly->assertDontSee('Berita Acara (BAST)');
        $resSidebarReadOnly->assertDontSee('Manajemen Pengguna');
        $resSidebarReadOnly->assertDontSee('Manajemen Satker');
        $resSidebarReadOnly->assertDontSee('Profil Saya');

        // 9. Reset back to home satker (Provinsi): read-only is OFF and management menus reappear
        $resSidebarHome = $this->actingAs($this->superAdmin)
            ->withSession(['active_satker_id' => $this->satkerProvinsi->id])
            ->get(route('dashboard'));
        $resSidebarHome->assertStatus(200);
        $resSidebarHome->assertSee('Manajemen Pengguna');
        $resSidebarHome->assertSee('Manajemen Satker');
        $resSidebarHome->assertSee('Profil Saya');

        $this->assertFalse(SatkerService::isViewingOtherSatker());
        $this->assertTrue(SatkerService::canManageCurrentScope());
    }

    public function test_user_management_displays_provinsi_gorontalo_for_provincial_users(): void
    {
        $this->assertEquals('Provinsi Gorontalo', $this->satkerProvinsi->wilayah_nama);

        $response = $this->actingAs($this->superAdmin)
            ->withSession(['active_satker_id' => $this->satkerProvinsi->id])
            ->get(route('users.index'));

        $response->assertStatus(200);
        $response->assertSee('Provinsi Gorontalo');
    }

    public function test_user_management_is_isolated_between_satkers(): void
    {
        // 1. In Provinsi context, only provincial users are shown
        $resProv = $this->actingAs($this->superAdmin)
            ->withSession(['active_satker_id' => $this->satkerProvinsi->id])
            ->get(route('users.index'));

        $resProv->assertStatus(200);
        $resProv->assertSee($this->adminProvinsi->name);
        $resProv->assertDontSee($this->userBoalemo->name);

        // 2. Switched to Boalemo context, only Boalemo users are shown
        $resBoalemo = $this->actingAs($this->superAdmin)
            ->withSession(['active_satker_id' => $this->satkerBoalemo->id])
            ->get(route('users.index'));

        $resBoalemo->assertStatus(200);
        $resBoalemo->assertSee($this->userBoalemo->name);
        $resBoalemo->assertDontSee($this->adminProvinsi->name);

        // 3. SuperAdmin can create a new user for Boalemo while in Boalemo context
        $resStore = $this->actingAs($this->superAdmin)
            ->withSession(['active_satker_id' => $this->satkerBoalemo->id])
            ->post(route('users.store'), [
                'name' => 'Admin Baru Boalemo',
                'username' => 'admin.boalemo.baru',
                'role' => 'Admin',
                'status' => 'active',
            ]);
        $resStore->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'username' => 'admin.boalemo.baru',
            'satker_id' => $this->satkerBoalemo->id,
            'role' => 'Admin',
        ]);

        // 4. Regency Admin cannot update or delete users from other satkers
        $adminBoalemo = User::where('username', 'admin.boalemo.baru')->firstOrFail();

        $resUpdateCrossSatker = $this->actingAs($adminBoalemo)
            ->put(route('users.update', $this->userPohuwato->id), [
                'name' => 'Hacked Pohuwato User',
                'username' => $this->userPohuwato->username,
                'role' => 'Staff',
                'status' => 'active',
            ]);
        $resUpdateCrossSatker->assertStatus(403);

        $resDeleteCrossSatker = $this->actingAs($adminBoalemo)
            ->delete(route('users.destroy', $this->userPohuwato->id));
        $resDeleteCrossSatker->assertStatus(403);
    }
}
