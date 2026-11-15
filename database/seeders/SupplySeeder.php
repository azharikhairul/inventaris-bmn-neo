<?php

namespace Database\Seeders;

use App\Models\Supply;
use Illuminate\Database\Seeder;

class SupplySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'kode_barang' => 'ATK-001',
                'nama_barang' => 'Kertas HVS A4 80 gram (PaperOne / Sinar Dunia)',
                'kategori' => 'Kertas & Dokumen',
                'satuan' => 'Rim',
                'stok' => 45,
                'stok_minimum' => 10,
                'spesifikasi' => 'Ukuran 210 x 297 mm, 80 gsm, isi 500 lembar per rim',
                'harga_estimasi' => 55000,
            ],
            [
                'kode_barang' => 'ATK-002',
                'nama_barang' => 'Kertas HVS F4 / Folio 75 gram',
                'kategori' => 'Kertas & Dokumen',
                'satuan' => 'Rim',
                'stok' => 30,
                'stok_minimum' => 8,
                'spesifikasi' => 'Ukuran 215 x 330 mm, 75 gsm, isi 500 lembar',
                'harga_estimasi' => 60000,
            ],
            [
                'kode_barang' => 'ATK-003',
                'nama_barang' => 'Tinta Printer Epson 003 Hitam Original',
                'kategori' => 'Komputer & IT',
                'satuan' => 'Botol',
                'stok' => 18,
                'stok_minimum' => 5,
                'spesifikasi' => 'Untuk printer seri L3110 / L3210 / L5190, 65ml',
                'harga_estimasi' => 95000,
            ],
            [
                'kode_barang' => 'ATK-004',
                'nama_barang' => 'Tinta Printer Epson 003 Warna (Cyan, Magenta, Yellow)',
                'kategori' => 'Komputer & IT',
                'satuan' => 'Set',
                'stok' => 12,
                'stok_minimum' => 4,
                'spesifikasi' => 'Set isi 3 warna @65ml',
                'harga_estimasi' => 275000,
            ],
            [
                'kode_barang' => 'ATK-005',
                'nama_barang' => 'Toner Cartridge HP LaserJet 85A (CE285A)',
                'kategori' => 'Komputer & IT',
                'satuan' => 'Pcs',
                'stok' => 6,
                'stok_minimum' => 2,
                'spesifikasi' => 'Untuk printer HP LaserJet P1102 / M1132 / M1212',
                'harga_estimasi' => 450000,
            ],
            [
                'kode_barang' => 'ATK-006',
                'nama_barang' => 'Pulpen Gel Faster C600 0.5mm Hitam',
                'kategori' => 'Alat Tulis Kantor (ATK)',
                'satuan' => 'Box',
                'stok' => 25,
                'stok_minimum' => 5,
                'spesifikasi' => '1 box isi 12 pcs pulpen tinta hitam',
                'harga_estimasi' => 38000,
            ],
            [
                'kode_barang' => 'ATK-007',
                'nama_barang' => 'Map Snelhefter Kertas Folio (Warna Biru BPS)',
                'kategori' => 'Kertas & Dokumen',
                'satuan' => 'Pak',
                'stok' => 15,
                'stok_minimum' => 5,
                'spesifikasi' => '1 pak isi 50 lembar, ada penjepit kertas',
                'harga_estimasi' => 85000,
            ],
            [
                'kode_barang' => 'ATK-008',
                'nama_barang' => 'Map Gantung File Odner Folio',
                'kategori' => 'Kertas & Dokumen',
                'satuan' => 'Pcs',
                'stok' => 40,
                'stok_minimum' => 10,
                'spesifikasi' => 'Odner bantex/gobi ukuran folio tebal 7cm',
                'harga_estimasi' => 32000,
            ],
            [
                'kode_barang' => 'ATK-009',
                'nama_barang' => 'Flashdisk Kingston USB 3.2 64GB',
                'kategori' => 'Komputer & IT',
                'satuan' => 'Unit',
                'stok' => 14,
                'stok_minimum' => 5,
                'spesifikasi' => 'Kingston DataTraveler Exodia 64GB USB 3.2 Gen 1',
                'harga_estimasi' => 75000,
            ],
            [
                'kode_barang' => 'ATK-010',
                'nama_barang' => 'Baterai Alkaline AA (Isi 6 pcs)',
                'kategori' => 'Lainnya',
                'satuan' => 'Pak',
                'stok' => 20,
                'stok_minimum' => 5,
                'spesifikasi' => 'Baterai ukuran AA 1.5V untuk mouse / pointer / jam',
                'harga_estimasi' => 42000,
            ],
            [
                'kode_barang' => 'ATK-011',
                'nama_barang' => 'Baterai Alkaline AAA (Isi 6 pcs)',
                'kategori' => 'Lainnya',
                'satuan' => 'Pak',
                'stok' => 18,
                'stok_minimum' => 5,
                'spesifikasi' => 'Baterai ukuran AAA 1.5V untuk remote AC / presenter',
                'harga_estimasi' => 42000,
            ],
            [
                'kode_barang' => 'ATK-012',
                'nama_barang' => 'Spidol Whiteboard Snowman Hitam',
                'kategori' => 'Alat Tulis Kantor (ATK)',
                'satuan' => 'Box',
                'stok' => 10,
                'stok_minimum' => 3,
                'spesifikasi' => '1 box isi 12 pcs, refillable whiteboard marker',
                'harga_estimasi' => 95000,
            ],
            [
                'kode_barang' => 'ATK-013',
                'nama_barang' => 'Sticky Notes / Post-it 3x3 Inch Warna Neon',
                'kategori' => 'Alat Tulis Kantor (ATK)',
                'satuan' => 'Pak',
                'stok' => 35,
                'stok_minimum' => 8,
                'spesifikasi' => 'Ukuran 76 x 76 mm isi 100 lembar warna cerah',
                'harga_estimasi' => 12500,
            ],
            [
                'kode_barang' => 'ATK-014',
                'nama_barang' => 'Stapler HD-10 & Isi Staples No.10',
                'kategori' => 'Alat Tulis Kantor (ATK)',
                'satuan' => 'Set',
                'stok' => 22,
                'stok_minimum' => 5,
                'spesifikasi' => '1 unit staples MAX HD-10 + 2 kotak anak staples No.10',
                'harga_estimasi' => 28000,
            ],
            [
                'kode_barang' => 'ATK-015',
                'nama_barang' => 'Cairan Pembersih Layar & Komputer Kit 100ml',
                'kategori' => 'Alat Kebersihan',
                'satuan' => 'Botol',
                'stok' => 15,
                'stok_minimum' => 4,
                'spesifikasi' => 'Pembersih layar LCD/Monitor + kain microfiber',
                'harga_estimasi' => 25000,
            ],
        ];

        foreach ($items as $item) {
            $supply = Supply::updateOrCreate(
                ['kode_barang' => $item['kode_barang']],
                $item
            );

            // Buat catatan saldo awal mutasi stok
            if ($supply->mutasis()->count() === 0 && $supply->stok > 0) {
                $supply->mutasis()->create([
                    'jenis' => 'masuk',
                    'jumlah' => $supply->stok,
                    'stok_sebelum' => 0,
                    'stok_sesudah' => $supply->stok,
                    'tanggal' => now()->toDateString(),
                    'keterangan' => 'Saldo awal persediaan BPS',
                    'user_id' => 1,
                ]);
            }
        }
    }
}
