<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateDummyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dummy:generate 
                            {--count=950000 : Jumlah aset yang ingin ditambahkan}
                            {--permintaan=25000 : Jumlah permintaan baru/pending (tanpa realisasi, tampil di halaman Permintaan)}
                            {--transaksi=10000 : Jumlah transaksi realisasi tambahan}
                            {--chunk=1500 : Ukuran batch insert ke database (aman dari limit 65.535 placeholder MySQL)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate data dummy skala besar (hingga jutaan) untuk aset, permintaan pending, dan realisasi';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $totalAsets = (int) $this->option('count');
        $totalPermintaanPending = (int) $this->option('permintaan');
        $totalTransaksi = (int) $this->option('transaksi');
        $chunkSize = (int) $this->option('chunk');

        $this->info('=================================================');
        $this->info('  GENERATOR DATA DUMMY INVENTARIS BMN (HIGH PERFORMANCE)');
        $this->info('=================================================');
        $this->line('Target Tambah Aset        : '.number_format($totalAsets, 0, ',', '.').' data');
        $this->line('Target Permintaan Pending : '.number_format($totalPermintaanPending, 0, ',', '.').' data (Tampil di menu Permintaan)');
        $this->line('Target Transaksi Realisasi: '.number_format($totalTransaksi, 0, ',', '.').' data');
        $this->line('Ukuran Batch/Chunk        : '.number_format($chunkSize, 0, ',', '.').' baris per insert');
        $this->newLine();

        // Optimasi PHP & MySQL
        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        DB::disableQueryLog();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $startTime = microtime(true);

        // 1. GENERATE ASET
        if ($totalAsets > 0) {
            $this->generateAsets($totalAsets, $chunkSize);
        }

        // 2. GENERATE PERMINTAAN PENDING (Tanpa Realisasi -> Muncul di Halaman Permintaan)
        if ($totalPermintaanPending > 0) {
            $this->generatePermintaanPending($totalPermintaanPending, $chunkSize);
        }

        // 3. GENERATE TRANSAKSI REALISASI (dengan variasi Siap Realisasi & Selesai)
        if ($totalTransaksi > 0) {
            $this->generateTransaksi($totalTransaksi, $chunkSize);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $elapsed = round(microtime(true) - $startTime, 2);
        $this->newLine();
        $this->info("SUKSES! Seluruh data dummy selesai dibuat dalam {$elapsed} detik.");
        $this->info('Total Aset saat ini          : '.number_format(DB::table('asets')->count(), 0, ',', '.'));
        $this->info('Total Permintaan di menu     : '.number_format(DB::table('permintaans')->whereNotExists(function ($q) {
            $q->select(DB::raw(1))->from('realisasis')->whereColumn('realisasis.permintaan_id', 'permintaans.id');
        })->count(), 0, ',', '.'));
        $this->info('Total Permintaan keseluruhan : '.number_format(DB::table('permintaans')->count(), 0, ',', '.'));
        $this->info('Total Realisasi saat ini     : '.number_format(DB::table('realisasis')->count(), 0, ',', '.'));

        return Command::SUCCESS;
    }

    protected function generateAsets(int $total, int $chunkSize)
    {
        $this->info('▶ Memulai penambahan '.number_format($total, 0, ',', '.').' data Aset...');

        $barangPool = [
            ['kode' => '3050204004', 'nama' => 'Air Conditioner (AC) Split', 'tipe' => 'Wall Mounted 1 PK'],
            ['kode' => '3050204005', 'nama' => 'Air Conditioner (AC) Standing', 'tipe' => 'Floor Standing 3 PK'],
            ['kode' => '3050204006', 'nama' => 'Air Conditioner (AC) Cassette', 'tipe' => 'Ceiling Cassette 2 PK'],
            ['kode' => '3050201001', 'nama' => 'Sepeda Motor', 'tipe' => 'Matic 125 cc'],
            ['kode' => '3050201002', 'nama' => 'Mobil Dinas Operasional', 'tipe' => 'MPV 1500 cc'],
            ['kode' => '3100102001', 'nama' => 'Personal Computer (PC)', 'tipe' => 'Desktop All-in-One'],
            ['kode' => '3100102002', 'nama' => 'Laptop / Notebook', 'tipe' => 'Core i7 16GB RAM'],
            ['kode' => '3100102003', 'nama' => 'Printer Laserjet', 'tipe' => 'Multifunction Color'],
            ['kode' => '3100203001', 'nama' => 'Server Rackmount', 'tipe' => '2U Dual Xeon 64GB'],
            ['kode' => '3050104001', 'nama' => 'Genset Diesel Otomatis', 'tipe' => 'Silent Type 50 KVA'],
            ['kode' => '3050105001', 'nama' => 'CCTV Surveillance Camera', 'tipe' => 'IP Dome 4MP Infrared'],
            ['kode' => '3020101001', 'nama' => 'Gedung Kantor Permanen', 'tipe' => 'Gedung Bertingkat'],
            ['kode' => '3050206001', 'nama' => 'Meja Kerja Pegawai', 'tipe' => 'Partisi Moduler Kayu'],
            ['kode' => '3050206002', 'nama' => 'Kursi Ergonomis Kantor', 'tipe' => 'Hydrolic Swivel Chair'],
            ['kode' => '3100201001', 'nama' => 'Switch Hub Gigabit', 'tipe' => '24 Port PoE Managed'],
        ];

        $merkPool = ['Panasonic', 'Daikin', 'LG', 'Samsung', 'Sharp', 'Honda', 'Toyota', 'Dell', 'HP', 'Lenovo', 'Asus', 'Epson', 'Canon', 'Hikvision', 'Cisco', 'Futura', 'Informa'];
        $kondisiPool = ['Baik', 'Baik', 'Baik', 'Baik', 'Rusak Ringan', 'Rusak Berat'];
        $ruangPool = ['Ruang Kepala BPS', 'Ruang Bagian Umum', 'Ruang Nerwilis', 'Ruang IPDS', 'Ruang Statistik Sosial', 'Ruang Statistik Distribusi', 'Ruang Statistik Produksi', 'Ruang Rapat Utama', 'Ruang Server', 'Lobby / PTSP', 'Gudang BMN', 'Pos Satpam', 'Aula Serbaguna'];

        $barangPoolCount = count($barangPool);
        $merkPoolCount = count($merkPool);
        $kondisiPoolCount = count($kondisiPool);
        $ruangPoolCount = count($ruangPool);

        $now = now()->toDateTimeString();
        $batches = (int) ceil($total / $chunkSize);
        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(' [%bar%] %percent:3s%% -- %current%/%max% data aset (%elapsed% / est %estimated%)');
        $bar->start();

        $currentMaxId = (int) (DB::table('asets')->max('id') ?? 0);

        for ($b = 0; $b < $batches; $b++) {
            $countThisBatch = min($chunkSize, $total - ($b * $chunkSize));
            $rows = [];

            for ($i = 0; $i < $countThisBatch; $i++) {
                $itemIndex = ($b * $chunkSize) + $i + 1;
                $nupNumber = $currentMaxId + $itemIndex;

                $bInfo = $barangPool[($itemIndex % $barangPoolCount)];
                $merk = $merkPool[($itemIndex % $merkPoolCount)];
                $kondisi = $kondisiPool[($itemIndex % $kondisiPoolCount)];
                $ruang = $ruangPool[($itemIndex % $ruangPoolCount)];

                $tahunPerolehan = rand(2015, 2025);
                $tglPerolehan = "{$tahunPerolehan}-0".rand(1, 9).'-'.rand(10, 28);
                $nilaiPerolehan = rand(20, 300) * 100000;
                $nilaiPenyusutan = rand(1, 15) * 100000;
                $nilaiBuku = max(0, $nilaiPerolehan - $nilaiPenyusutan);

                $rows[] = [
                    'no' => (string) $itemIndex,
                    'jenis_bmn' => 'Aset Tetap',
                    'kode_satker' => '636900',
                    'nama_satker' => 'BPS PROVINSI GORONTALO',
                    'kode_barang' => $bInfo['kode'],
                    'nup' => (string) $nupNumber,
                    'nama_barang' => $bInfo['nama'],
                    'merk' => $merk,
                    'tipe' => $bInfo['tipe'],
                    'kondisi' => $kondisi,
                    'umur_aset' => (2026 - $tahunPerolehan),
                    'intra_extra' => 'Intrakomptabel',
                    'henti_guna' => 'Tidak',
                    'status_sbsn' => 'Non SBSN',
                    'status_bmn_idle' => 'Bukan BMN Idle',
                    'status_kemitraan' => 'Tidak Ada Kemitraan',
                    'tanggal_perolehan' => $tglPerolehan,
                    'nilai_perolehan' => $nilaiPerolehan,
                    'nilai_penyusutan' => $nilaiPenyusutan,
                    'nilai_buku' => $nilaiBuku,
                    'status_penggunaan' => 'Digunakan sendiri untuk operasional',
                    'provinsi' => 'Gorontalo',
                    'kode_provinsi' => '75',
                    'kab_kota' => 'Kota Gorontalo',
                    'lokasi_ruang' => $ruang,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('asets')->insert($rows);
            $bar->advance($countThisBatch);
            unset($rows);
        }

        $bar->finish();
        $this->newLine();
        $this->info('✓ Data Aset berhasil dibuat.');
    }

    protected function generatePermintaanPending(int $total, int $chunkSize)
    {
        $this->info('▶ Memulai pembuatan '.number_format($total, 0, ',', '.').' data Permintaan Aktif/Pending...');

        $asetIds = DB::table('asets')->limit(5000)->pluck('id')->toArray();
        if (empty($asetIds)) {
            $this->warn('Tidak ada aset ditemukan.');

            return;
        }
        $totalAsetIds = count($asetIds);

        $pekerjaanPool = [
            'Pemeliharaan Rutin & Service AC Split Ruang Rapat',
            'Service Berkala dan Perbaikan AC Standing Ruang Kepala',
            'Perbaikan Instalasi Jaringan LAN dan Akses Wifi Gedung',
            'Perawatan Rutin Kendaraan Roda 4 Operasional Kantor',
            'Penggantian Baterai & Perbaikan UPS Ruang Server',
            'Pengecatan dan Perbaikan Dinding Ruang Nerwilis',
            'Perbaikan Meja Kerja & Penggantian Kunci Laci',
            'Service Rutin Motor Operasional Satker',
            'Perbaikan Mesin Genset & Penggantian Filter Solar',
            'Pembersihan Filter & Perbaikan Pipa Pembuangan AC',
        ];

        $jasaItems = [
            ['uraian' => 'Cuci AC Split Wall 1 PK', 'harga' => 75000, 'satuan' => 'Unit'],
            ['uraian' => 'Cuci AC Standing / Cassette 2 PK', 'harga' => 125000, 'satuan' => 'Unit'],
            ['uraian' => 'Jasa Perbaikan Konektor LAN', 'harga' => 50000, 'satuan' => 'Titik'],
            ['uraian' => 'Jasa Service Mesin Berkala', 'harga' => 200000, 'satuan' => 'Kegiatan'],
            ['uraian' => 'Jasa Pengecatan Dinding', 'harga' => 350000, 'satuan' => 'Ruangan'],
        ];

        $sparepartItems = [
            ['uraian' => 'Kapasitor Fan Indoor', 'harga' => 175000, 'satuan' => 'Pcs'],
            ['uraian' => 'Sensor Remote AC', 'harga' => 150000, 'satuan' => 'Pcs'],
            ['uraian' => 'Pengisian Freon R410A', 'harga' => 250000, 'satuan' => 'Paket'],
            ['uraian' => 'Filter Udara Baru', 'harga' => 85000, 'satuan' => 'Pcs'],
            ['uraian' => 'Kabel UTP Cat6 50M', 'harga' => 250000, 'satuan' => 'Paket'],
        ];

        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(' [%bar%] %percent:3s%% -- %current%/%max% permintaan pending (%elapsed% / est %estimated%)');
        $bar->start();

        $batches = (int) ceil($total / $chunkSize);
        $currentPermintaanMaxId = (int) (DB::table('permintaans')->max('id') ?? 0);
        $currentPermintaanAsetMaxId = (int) (DB::table('permintaan_asets')->max('id') ?? 0);

        for ($b = 0; $b < $batches; $b++) {
            $countThisBatch = min($chunkSize, $total - ($b * $chunkSize));

            $permintaanRows = [];
            $permintaanAsetRows = [];
            $rabItemRows = [];

            for ($i = 0; $i < $countThisBatch; $i++) {
                $globalIndex = ($b * $chunkSize) + $i + 1;
                $pId = $currentPermintaanMaxId + $globalIndex;
                $paId = $currentPermintaanAsetMaxId + $globalIndex;
                $asetId = $asetIds[$globalIndex % $totalAsetIds];

                $tgl = Carbon::now()->subDays(rand(0, 180));
                $tglStr = $tgl->format('Y-m-d');

                $pekerjaan = $pekerjaanPool[$globalIndex % count($pekerjaanPool)];
                $jenis = ($globalIndex % 4 === 0) ? 'Supply' : 'Maintenance';

                $jasa = $jasaItems[$globalIndex % count($jasaItems)];
                $part = $sparepartItems[$globalIndex % count($sparepartItems)];

                $volJasa = rand(1, 2);
                $totalJasa = $volJasa * $jasa['harga'];
                $volPart = rand(1, 2);
                $totalPart = $volPart * $part['harga'];
                $totalRab = $totalJasa + $totalPart;

                $permintaanRows[] = [
                    'id' => $pId,
                    'pekerjaan' => $pekerjaan,
                    'jenis' => $jenis,
                    'total_rab' => $totalRab,
                    'created_at' => $tglStr,
                    'updated_at' => $tglStr,
                ];

                $permintaanAsetRows[] = [
                    'id' => $paId,
                    'permintaan_id' => $pId,
                    'aset_id' => $asetId,
                    'deskripsi_kerusakan' => 'Performa menurun dan butuh pengecekan berkala teknisi',
                    'kondisi_aset' => 'Rusak Ringan',
                    'created_at' => $tglStr,
                    'updated_at' => $tglStr,
                ];

                $rabItemRows[] = [
                    'permintaan_aset_id' => $paId,
                    'uraian' => $jasa['uraian'],
                    'volume' => $volJasa,
                    'satuan' => $jasa['satuan'],
                    'harga_satuan' => $jasa['harga'],
                    'jumlah' => $totalJasa,
                    'created_at' => $tglStr,
                    'updated_at' => $tglStr,
                ];

                $rabItemRows[] = [
                    'permintaan_aset_id' => $paId,
                    'uraian' => $part['uraian'],
                    'volume' => $volPart,
                    'satuan' => $part['satuan'],
                    'harga_satuan' => $part['harga'],
                    'jumlah' => $totalPart,
                    'created_at' => $tglStr,
                    'updated_at' => $tglStr,
                ];
            }

            DB::table('permintaans')->insert($permintaanRows);
            DB::table('permintaan_asets')->insert($permintaanAsetRows);
            DB::table('rab_items')->insert($rabItemRows);

            $bar->advance($countThisBatch);
            unset($permintaanRows, $permintaanAsetRows, $rabItemRows);
        }

        $bar->finish();
        $this->newLine();
        $this->info('✓ Data Permintaan Pending berhasil dibuat (muncul di menu Permintaan).');
    }

    protected function generateTransaksi(int $total, int $chunkSize)
    {
        $this->info('▶ Memulai pembuatan '.number_format($total, 0, ',', '.').' data Transaksi Realisasi...');

        $asetIds = DB::table('asets')->limit(5000)->pluck('id')->toArray();
        if (empty($asetIds)) {
            $this->warn('Tidak ada aset ditemukan.');

            return;
        }
        $totalAsetIds = count($asetIds);

        $pekerjaanPool = [
            'Pemeliharaan Rutin & Service AC Split Ruangan',
            'Service Berkala dan Penggantian Sparepart AC Cassette',
            'Perbaikan Jaringan LAN & Pergantian Switch Hub',
            'Perawatan Rutin Kendaraan Operasional Dinas Roda 4',
            'Service Rutin Kendaraan Bermotor Roda 2 Satker',
            'Penggantian Komponen Hardware Komputer & Laptop',
            'Perbaikan dan Pengecatan Dinding Ruang Rapat',
            'Penggantian Baterai & Maintenance UPS Server',
            'Penggantian Suku Cadang & Service Genset Kantor',
            'Pembersihan & Perbaikan Pompa Air dan Tandon Gedung',
        ];

        $vendorPool = [
            'CV. Gorontalo Teknik Mandiri',
            'PT. Bintang Sejahtera Gorontalo',
            'CV. Maju Abadi Sentosa',
            'Bengkel AC Sejuk Abadi',
            'PT. Multitech Solusindo',
            'CV. Cipta Karya Perkasa',
            'PT. Anugerah BMN Servisindo',
            'CV. Elektronika Prima',
        ];

        $sparepartItems = [
            ['uraian' => 'Kapasitor Fan Indoor', 'harga' => 175000, 'satuan' => 'Pcs'],
            ['uraian' => 'Sensor Remote Receiver', 'harga' => 150000, 'satuan' => 'Pcs'],
            ['uraian' => 'Pengisian Freon R410A Full', 'harga' => 250000, 'satuan' => 'Paket'],
            ['uraian' => 'Kompresor AC 1 PK', 'harga' => 1250000, 'satuan' => 'Unit'],
            ['uraian' => 'Penggantian Pipa Tembaga AC', 'harga' => 120000, 'satuan' => 'Meter'],
            ['uraian' => 'Penggantian Filter Udara AC', 'harga' => 85000, 'satuan' => 'Pcs'],
            ['uraian' => 'Ganti Oli Mesin & Filter Oli', 'harga' => 450000, 'satuan' => 'Paket'],
            ['uraian' => 'Aki Kering 12V 45Ah', 'harga' => 850000, 'satuan' => 'Unit'],
            ['uraian' => 'Kabel UTP Cat6 Roll', 'harga' => 950000, 'satuan' => 'Roll'],
        ];

        $jasaItems = [
            ['uraian' => 'Cuci AC Split Wall 1 PK', 'harga' => 75000, 'satuan' => 'Unit'],
            ['uraian' => 'Cuci AC Standing / Cassette 2 PK', 'harga' => 125000, 'satuan' => 'Unit'],
            ['uraian' => 'Jasa Bongkar Pasang Unit AC', 'harga' => 300000, 'satuan' => 'Unit'],
            ['uraian' => 'Jasa Tune Up & Tune Engine Kendaraan', 'harga' => 350000, 'satuan' => 'Kendaraan'],
            ['uraian' => 'Jasa Pemasangan & Crimping Kabel LAN', 'harga' => 150000, 'satuan' => 'Titik'],
            ['uraian' => 'Jasa Troubleshooting Server & Backup', 'harga' => 500000, 'satuan' => 'Kegiatan'],
            ['uraian' => 'Jasa Perbaikan Instalasi Listrik Ruang', 'harga' => 250000, 'satuan' => 'Ruang'],
        ];

        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(' [%bar%] %percent:3s%% -- %current%/%max% transaksi (%elapsed% / est %estimated%)');
        $bar->start();

        $batches = (int) ceil($total / $chunkSize);
        $currentPermintaanMaxId = (int) (DB::table('permintaans')->max('id') ?? 0);
        $currentRealisasiMaxId = (int) (DB::table('realisasis')->max('id') ?? 0);
        $currentPermintaanAsetMaxId = (int) (DB::table('permintaan_asets')->max('id') ?? 0);
        $currentRealisasiAsetMaxId = (int) (DB::table('realisasi_asets')->max('id') ?? 0);

        for ($b = 0; $b < $batches; $b++) {
            $countThisBatch = min($chunkSize, $total - ($b * $chunkSize));

            $permintaanRows = [];
            $permintaanAsetRows = [];
            $rabItemRows = [];

            $realisasiRows = [];
            $realisasiAsetRows = [];
            $realisasiItemRows = [];

            for ($i = 0; $i < $countThisBatch; $i++) {
                $globalIndex = ($b * $chunkSize) + $i + 1;
                $pId = $currentPermintaanMaxId + $globalIndex;
                $rId = $currentRealisasiMaxId + $globalIndex;
                $paId = $currentPermintaanAsetMaxId + $globalIndex;
                $raId = $currentRealisasiAsetMaxId + $globalIndex;

                $asetId = $asetIds[$globalIndex % $totalAsetIds];

                $tgl = Carbon::now()->subDays(rand(1, 720));
                $tglPengajuan = $tgl->format('Y-m-d');
                $tglMulai = $tgl->copy()->addDays(2)->format('Y-m-d');
                $tglSelesai = $tgl->copy()->addDays(rand(3, 7))->format('Y-m-d');
                $year = $tgl->format('Y');

                $pekerjaan = $pekerjaanPool[$globalIndex % count($pekerjaanPool)];
                $vendor = $vendorPool[$globalIndex % count($vendorPool)];
                $noFpa = "FP-{$year}-636900-92000-".str_pad((string) $rId, 4, '0', STR_PAD_LEFT);

                // Status variasi: 50% Siap Realisasi, 50% Selesai
                $status = ($globalIndex % 2 === 0) ? 'siap realisasi' : 'Selesai';

                $jasa = $jasaItems[$globalIndex % count($jasaItems)];
                $part = $sparepartItems[$globalIndex % count($sparepartItems)];

                $volJasa = rand(1, 3);
                $totalJasa = $volJasa * $jasa['harga'];
                $volPart = rand(1, 2);
                $totalPart = $volPart * $part['harga'];
                $totalBiaya = $totalJasa + $totalPart;

                // 1. Permintaan
                $permintaanRows[] = [
                    'id' => $pId,
                    'pekerjaan' => $pekerjaan,
                    'jenis' => 'Maintenance',
                    'total_rab' => $totalBiaya,
                    'created_at' => $tglPengajuan,
                    'updated_at' => $tglPengajuan,
                ];

                $permintaanAsetRows[] = [
                    'id' => $paId,
                    'permintaan_id' => $pId,
                    'aset_id' => $asetId,
                    'deskripsi_kerusakan' => 'Kerusakan fungsi dan penurunan performa, membutuhkan perbaikan berkala',
                    'kondisi_aset' => 'Rusak Ringan',
                    'created_at' => $tglPengajuan,
                    'updated_at' => $tglPengajuan,
                ];

                $rabItemRows[] = [
                    'permintaan_aset_id' => $paId,
                    'uraian' => $jasa['uraian'],
                    'volume' => $volJasa,
                    'satuan' => $jasa['satuan'],
                    'harga_satuan' => $jasa['harga'],
                    'jumlah' => $totalJasa,
                    'created_at' => $tglPengajuan,
                    'updated_at' => $tglPengajuan,
                ];

                $rabItemRows[] = [
                    'permintaan_aset_id' => $paId,
                    'uraian' => $part['uraian'],
                    'volume' => $volPart,
                    'satuan' => $part['satuan'],
                    'harga_satuan' => $part['harga'],
                    'jumlah' => $totalPart,
                    'created_at' => $tglPengajuan,
                    'updated_at' => $tglPengajuan,
                ];

                // 2. Realisasi
                $realisasiRows[] = [
                    'id' => $rId,
                    'permintaan_id' => $pId,
                    'no_fpa' => $noFpa,
                    'vendor' => $vendor,
                    'pekerjaan' => $pekerjaan,
                    'jenis' => 'Maintenance',
                    'tanggal_pengajuan' => $tglPengajuan,
                    'tanggal_mulai' => $tglMulai,
                    'tanggal_selesai' => ($status === 'Selesai') ? $tglSelesai : null,
                    'total_realisasi' => $totalBiaya,
                    'status' => $status,
                    'kak_file' => null,
                    'kuitansi_file' => null,
                    'created_at' => $tglPengajuan,
                    'updated_at' => ($status === 'Selesai') ? $tglSelesai : $tglPengajuan,
                ];

                $realisasiAsetRows[] = [
                    'id' => $raId,
                    'realisasi_id' => $rId,
                    'aset_id' => $asetId,
                    'nama_aset' => 'Aset BMN',
                    'kode_inventaris' => (string) ($asetId),
                    'deskripsi_kerusakan' => 'Dilakukan perbaikan dan penggantian suku cadang',
                    'foto_dokumentasi' => null,
                    'subtotal' => $totalBiaya,
                    'created_at' => $tglPengajuan,
                    'updated_at' => ($status === 'Selesai') ? $tglSelesai : $tglPengajuan,
                ];

                $realisasiItemRows[] = [
                    'realisasi_aset_id' => $raId,
                    'uraian' => $jasa['uraian'],
                    'volume' => $volJasa,
                    'satuan' => $jasa['satuan'],
                    'harga_satuan' => $jasa['harga'],
                    'jumlah' => $totalJasa,
                    'created_at' => $tglPengajuan,
                    'updated_at' => ($status === 'Selesai') ? $tglSelesai : $tglPengajuan,
                ];

                $realisasiItemRows[] = [
                    'realisasi_aset_id' => $raId,
                    'uraian' => $part['uraian'],
                    'volume' => $volPart,
                    'satuan' => $part['satuan'],
                    'harga_satuan' => $part['harga'],
                    'jumlah' => $totalPart,
                    'created_at' => $tglPengajuan,
                    'updated_at' => ($status === 'Selesai') ? $tglSelesai : $tglPengajuan,
                ];
            }

            DB::table('permintaans')->insert($permintaanRows);
            DB::table('permintaan_asets')->insert($permintaanAsetRows);
            DB::table('rab_items')->insert($rabItemRows);

            DB::table('realisasis')->insert($realisasiRows);
            DB::table('realisasi_asets')->insert($realisasiAsetRows);
            DB::table('realisasi_items')->insert($realisasiItemRows);

            $bar->advance($countThisBatch);

            unset($permintaanRows, $permintaanAsetRows, $rabItemRows, $realisasiRows, $realisasiAsetRows, $realisasiItemRows);
        }

        $bar->finish();
        $this->newLine();
        $this->info('✓ Data Transaksi Realisasi berhasil dibuat.');
    }
}
