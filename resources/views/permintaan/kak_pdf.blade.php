<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Kerangka Acuan Kerja (KAK) - {{ $form['pekerjaan'] ?? 'Permintaan' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 18mm 20mm 18mm 20mm;
        }
        
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10.5pt;
            line-height: 1.45;
            color: #111111;
            margin: 0;
            padding: 0;
        }

        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .font-medium { font-weight: 600; }
        .uppercase { text-transform: uppercase; }

        .logo-container {
            text-align: center;
            margin-bottom: 12px;
        }
        
        .logo-img {
            height: 52px;
            width: auto;
        }

        .doc-title {
            text-align: center;
            margin-bottom: 18px;
        }

        .doc-title h1 {
            font-size: 11.5pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 3px 0;
            line-height: 1.3;
        }

        .doc-title h2 {
            font-size: 11pt;
            font-weight: bold;
            margin: 0;
            line-height: 1.3;
        }

        .section-heading {
            font-size: 10.5pt;
            font-weight: bold;
            margin-top: 13px;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        p {
            margin: 0 0 6px 0;
            text-align: justify;
        }

        .indent {
            text-indent: 32px;
        }

        .page-break {
            page-break-before: always;
            clear: both;
        }

        /* Detail table */
        table.detail-table {
            width: 100%;
            margin-top: 6px;
            margin-bottom: 10px;
            font-size: 10pt;
            border: none;
        }

        table.detail-table td {
            border: none;
            padding: 2px 4px;
            vertical-align: top;
        }

        /* DIPA table for Supply */
        table.dipa-table {
            width: 100%;
            margin-top: 4px;
            margin-bottom: 8px;
            font-size: 10pt;
            border: none;
        }

        table.dipa-table td {
            border: none;
            padding: 2px 4px;
            vertical-align: top;
        }

        /* Spesifikasi Teknis */
        .spec-section {
            margin-top: 5px;
            margin-bottom: 5px;
        }

        .spec-section .sub-heading {
            font-weight: 600;
            font-size: 10pt;
            margin-bottom: 3px;
        }

        .spec-section ul {
            margin: 2px 0 6px 20px;
            padding: 0;
        }

        .spec-section ul li {
            margin-bottom: 2px;
            font-size: 9.5pt;
            color: #333333;
        }

        /* Maintenance RAB Table (Cyan) */
        table.rab-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 15px;
            font-size: 9.5pt;
        }

        table.rab-table th {
            background-color: #4697cf;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            padding: 6px 5px;
            border: 1px solid #2b74a6;
            vertical-align: middle;
            text-transform: uppercase;
            font-size: 8.5pt;
            letter-spacing: 0.5px;
        }

        table.rab-table td {
            padding: 5px 6px;
            border: 1px solid #777777;
            vertical-align: middle;
        }

        table.rab-table tr:nth-child(even) td {
            background-color: #f9fbfd;
        }

        /* Supply RAB Table (Yellow Header matching official template) */
        table.rab-table-supply {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 15px;
            font-size: 9.5pt;
        }

        table.rab-table-supply th {
            background-color: #ffc000;
            color: #000000;
            font-weight: bold;
            text-align: center;
            padding: 6px 5px;
            border: 1px solid #000000;
            vertical-align: middle;
            font-size: 9pt;
        }

        table.rab-table-supply td {
            padding: 5px 6px;
            border: 1px solid #000000;
            vertical-align: middle;
        }

        /* Signatures */
        .signature-table {
            width: 100%;
            margin-top: 18px;
            border: none;
            page-break-inside: avoid;
        }

        .signature-table td {
            border: none;
            vertical-align: top;
            padding: 0;
            font-size: 10pt;
        }

        .sig-space {
            height: 65px;
        }
    </style>
</head>
<body>

@if($permintaan->isSupply())
    {{-- ========================================================= --}}
    {{-- TEMPLATE KAK PERSESUAIAN RESMI SUPPLY / PERSEDIAAN (PDF) --}}
    {{-- ========================================================= --}}

    <!-- ================= HALAMAN 1 (SUPPLY) ================= -->
    <div class="logo-container">
        @if(!empty($logoBase64))
            <img src="{{ $logoBase64 }}" alt="Logo KAK" class="logo-img">
        @elseif(file_exists(public_path('logo_KAK_opt.png')))
            <img src="{{ public_path('logo_KAK_opt.png') }}" alt="Logo KAK" class="logo-img">
        @elseif(file_exists(public_path('logo_KAK.png')))
            <img src="{{ public_path('logo_KAK.png') }}" alt="Logo KAK" class="logo-img">
        @elseif(file_exists(public_path('logo_bps.png')))
            <img src="{{ public_path('logo_bps.png') }}" alt="Logo BPS" class="logo-img">
        @endif
    </div>

    <div class="doc-title" style="margin-bottom: 18px;">
        <p style="font-size: 11pt; font-weight: bold; margin: 0 0 3px 0; text-align: center;">Analisis Kebutuhan Anggaran</p>
        <h1 style="font-size: 11pt; font-weight: bold; margin: 0 0 3px 0; text-align: center;">{{ $form['judul_dokumen'] ?? 'BELANJA KEPERLUAN SEHARI-HARI PERKANTORAN (PERSEDIAAN)' }}</h1>
        <h2 style="font-size: 11pt; font-weight: bold; margin: 0; text-align: center; text-transform: uppercase;">{{ $form['instansi'] ?? 'BPS PROV. GORONTALO' }} TA {{ $form['tahun_anggaran'] }}</h2>
    </div>

    <!-- A. LATAR BELAKANG -->
    <div class="section-heading">A. LATAR BELAKANG</div>
    <p class="indent">
        Badan Pusat Statistik adalah Lembaga Pemerintah Non Kementerian yang bertanggung jawab langsung kepada Presiden. BPS dibentuk berdasarkan UU Nomor 6 Tahun 1960 tentang Sensus dan UU Nomer 7 Tahun 1960 tentang Statistik. Sebagai pengganti kedua UU tersebut ditetapkan UU Nomor 16 Tahun 1997 tentang Statistik. Tugas, fungsi dan kewenangan BPS telah ditetapkan berdasarkan Peraturan Presiden Nomor 86 Tahun 2007 tentang Badan Pusat Statistik dan Peraturan Kepala Badan Pusat Statistik Nomor 7 Tahun 2008 tentang Organisasi dan Tata Kerja Badan Pusat Statistik. Tugas BPS adalah untuk melaksanakan tugas pemerintahan dibidang statistik sesuai peraturan perundang-undangan.
    </p>
    <p class="indent">
        Dalam rangka mendukung kelancaran pelaksanaan tugas pemerintahan di kantor Badan Pusat Statistik Provinsi Gorontalo diperlukan belanja kebutuhan dasar dan penunjang untuk operasional perkantoran berupa ATK dan Komputer Supplies. Kebutuhan tersebut merupakan komponen penting dalam menjalankan operasional sehari-hari kantor dan penunjang produktivitas/kinerja pegawai dan pelayanan publik yang prima kepada pihak eksternal.
    </p>

    <!-- B. MAKSUD DAN TUJUAN -->
    <div class="section-heading">B. MAKSUD DAN TUJUAN</div>
    <p class="indent">
        Maksud dan tujuan dari pelaksanaan kegiatan pemenuhan belanja barang keperluan operasional perkantoran di BPS Provinsi Gorontalo adalah agar tersedianya ATK dan komputer supplies untuk memperlancar kegiatan operasional untuk mendukung kegiatan survei dan sensus dan optimalisasi pelayanan publik dilingkungan kantor BPS Provinsi Gorontalo.
    </p>

    <!-- C. RUANG LINGKUP -->
    <div class="section-heading">C. RUANG LINGKUP</div>
    <p class="indent">
        Kegiatan meliputi pengadaan dan pembelian barang persediaan berupa ATK (alat Tulis Kantor) dan komputer (komputer supplies) yang merupakan elemen penting dalam penyelenggaraan operasional pendukung survei dan sensus.
    </p>

    <!-- D. PRODUK YANG DIHASILKAN -->
    <div class="section-heading">D. PRODUK YANG DIHASILKAN</div>
    <p class="indent">
        Barang persediaan berupa ATK dan Komputer Supplies sesuai dengan spesfikasi kebutuhan.
    </p>

    <!-- E. SUMBER PEMBIAYAAN -->
    <div class="section-heading">E. SUMBER PEMBIAYAAN</div>
    <p class="indent">
        Sumber pembiayaan adalah dari DIPA {{ $form['instansi'] ?? 'BPS Provinsi Gorontalo' }} dengan rincian anggaran biaya sebagai berikut:
    </p>
    <table class="dipa-table" style="margin-left: 0;">
        <tr>
            <td style="width: 130px;">Program</td>
            <td style="width: 10px;">:</td>
            <td>{{ $form['program'] ?? '054.01.GG. Program Penyediaan & Pelayanan Informasi Statistik' }}</td>
        </tr>
        <tr>
            <td>Kegiatan</td>
            <td>:</td>
            <td>{{ $form['kegiatan'] ?? '2902 BMA Publikasi/Laporan Sensus Ekonomi' }}</td>
        </tr>
        <tr>
            <td>Output</td>
            <td>:</td>
            <td>{{ $form['output'] ?? '006. Publikasi/Laporan Sensus Ekonomi' }}</td>
        </tr>
        <tr>
            <td>Komponen</td>
            <td>:</td>
            <td>{{ $form['komponen'] ?? '530 Pelaksanaan SE2026' }}</td>
        </tr>
        <tr>
            <td>Item Kegiatan</td>
            <td>:</td>
            <td>{{ $form['item_kegiatan'] ?? '521811/Belanja Barang Persediaan Barang Konsumsi' }}</td>
        </tr>
        <tr>
            <td>Total Pagu</td>
            <td>:</td>
            <td>Rp {{ number_format($form['total_pagu'] ?? $form['total_biaya'], 0, ',', '.') }},-</td>
        </tr>
    </table>

    <p style="margin-top: 8px; margin-bottom: 6px;">
        Estimasi biaya yang dibutuhkan pada kegiatan ini selama 1 tahun anggaran adalah sebagai berikut:
    </p>

    <table class="rab-table-supply">
        <thead>
            <tr>
                <th style="width: 6%;">No</th>
                <th style="width: 44%;">Uraian</th>
                <th style="width: 14%;">Unit/<br>Satuan</th>
                <th style="width: 8%;">Vol</th>
                <th style="width: 14%;">Harga<br>Satuan (Rp)</th>
                <th style="width: 14%;">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rabItems as $idx => $item)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>{{ $item['kegiatan'] }}</td>
                <td class="text-center">{{ $item['satuan'] }}</td>
                <td class="text-center">{{ $item['volume'] }}</td>
                <td class="text-right">{{ number_format($item['harga_satuan'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item['total'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td class="text-center">1</td>
                <td>{{ $form['pekerjaan'] }}</td>
                <td class="text-center">Paket</td>
                <td class="text-center">1</td>
                <td class="text-right">{{ number_format($form['total_biaya'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($form['total_biaya'], 0, ',', '.') }}</td>
            </tr>
            @endforelse
            <tr style="font-weight: bold; background-color: #ffffff;">
                <td colspan="5" class="text-center font-bold" style="padding: 6px; border: 1px solid #000000;">Jumlah</td>
                <td class="text-right font-bold" style="padding: 6px; border: 1px solid #000000;">{{ number_format($form['total_biaya'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- ================= HALAMAN 2 (SUPPLY) ================= -->
    <div class="page-break"></div>

    <!-- A. WAKTU PELAKSANAAN -->
    <div class="section-heading">A. WAKTU PELAKSANAAN</div>
    <table class="dipa-table" style="margin-left: 0; margin-bottom: 12px;">
        <tr>
            <td style="width: 190px;">Pelaksanaan Pengadaan</td>
            <td style="width: 10px;">:</td>
            <td>{{ $form['waktu_pengadaan'] ?? $form['waktu_pelaksanaan'] }}</td>
        </tr>
        <tr>
            <td>Pelaksanaan Kontrak</td>
            <td>:</td>
            <td>{{ $form['waktu_kontrak'] ?? $form['waktu_pelaksanaan'] }}</td>
        </tr>
        <tr>
            <td>Pemanfaatan hasil</td>
            <td>:</td>
            <td>{{ $form['pemanfaatan_hasil'] ?? $form['waktu_pelaksanaan'] }}</td>
        </tr>
    </table>

    <!-- B. PENUTUP -->
    <div class="section-heading">B. PENUTUP</div>
    <table style="width: 100%; border: none; font-size: 10pt; line-height: 1.5; margin-bottom: 25px;">
        <tr>
            <td style="width: 20px; vertical-align: top; border: none; padding: 2px 0;">1.</td>
            <td style="vertical-align: top; border: none; text-align: justify; padding: 2px 0;">
                Apabila terdapat hal-hal yang bertentangan dengan ketentuan, peraturan, pedoman, dan kebijaksanaan pemerintah yang berlaku, maka segala yang termaktub di dalam Kerangka Acuan Kegiatan (KAK) akan diteliti kembali.
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top; border: none; padding: 2px 0;">2.</td>
            <td style="vertical-align: top; border: none; text-align: justify; padding: 2px 0;">
                Hal-hal yang belum diatur dalam KAK akan ditetapkan lebih lanjut.
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top; border: none; padding: 2px 0;">3.</td>
            <td style="vertical-align: top; border: none; text-align: justify; padding: 2px 0;">
                Demikian KAK ini dibuat untuk dipergunakan semestinya.
            </td>
        </tr>
    </table>

    <!-- Tanda Tangan Supply (Format Persis PDF Halaman 2) -->
    <table class="signature-table" style="margin-top: 35px;">
        <tr>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                <div class="font-medium" style="margin-bottom: 4px;">Penanggung jawab kegiatan</div>
                <div class="sig-space"></div>
                <div class="font-bold">{{ $form['penanggung_jawab_nama'] }}</div>
                <div style="font-size: 9.5pt;">NIP. {{ $form['penanggung_jawab_nip'] }}</div>
            </td>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                <div style="margin-bottom: 2px;">{{ $form['kota'] }}, {{ $form['tanggal'] }}</div>
                <div class="font-medium" style="margin-bottom: 4px;">Pejabat Pembuat Komitmen</div>
                <div class="sig-space"></div>
                <div class="font-bold">{{ $form['ppk_nama'] }}</div>
                <div style="font-size: 9.5pt;">NIP. {{ $form['ppk_nip'] }}</div>
            </td>
        </tr>
    </table>

@else
    {{-- ========================================================= --}}
    {{-- TEMPLATE KAK PEMELIHARAAN (MAINTENANCE) ASLI TIDAK DIUBAH --}}
    {{-- ========================================================= --}}

    <!-- ================= HALAMAN 1 ================= -->
    <div class="logo-container">
        @if(!empty($logoBase64))
            <img src="{{ $logoBase64 }}" alt="Logo KAK" class="logo-img">
        @elseif(file_exists(public_path('logo_KAK_opt.png')))
            <img src="{{ public_path('logo_KAK_opt.png') }}" alt="Logo KAK" class="logo-img">
        @elseif(file_exists(public_path('logo_KAK.png')))
            <img src="{{ public_path('logo_KAK.png') }}" alt="Logo KAK" class="logo-img">
        @elseif(file_exists(public_path('logo_bps.png')))
            <img src="{{ public_path('logo_bps.png') }}" alt="Logo BPS" class="logo-img">
        @endif
    </div>

    <div class="doc-title">
        <h1>ANALISIS KEBUTUHAN ANGGARAN {{ $form['pekerjaan'] }}</h1>
        <h2>{{ $form['instansi'] }} TA {{ $form['tahun_anggaran'] }}</h2>
    </div>

    <!-- A. LATAR BELAKANG -->
    <div class="section-heading">A. LATAR BELAKANG</div>
    <p class="indent">
        Ketentuan dalam Peraturan Pemerintah Nomor 27 Tahun 2014 tentang Pengelolaan Barang Milik Negara / Daerah (Lembaga Negara Republik Indonesia Tahun 2014 Nomor 92, Tambahan Lembaran Negara Republik Indonesia Nomor 5533) Pasal 6 ayat (2) huruf (f) yang berbunyi "Pengguna Barang Milik Negara berwenang dan bertanggung jawab mengamankan dan memelihara Barang Milik Negara yang berada dalam penguasaannya".
    </p>
    <p class="indent">
        Berdasarkan Ketentuan Peraturan Pemerintah tersebut, maka perlu dilaksanakan kegiatan pengurusan dan pemeliharaan Barang Milik Negara berupa pemeliharaan peralatan dan mesin di lingkungan Kantor {{ $form['instansi'] }}.
    </p>
    <p class="indent">
        Pemeliharaan peralatan dan mesin berupa pemeliharaan mesin {{ $form['pekerjaan'] }} sangat penting serta memastikan aset tersebut tetap berfungsi dengan baik dan memiliki umur pakai yang optimal untuk mendukung operasional kantor dalam rangka meningkatkan produktivitas dan kinerja pegawai, dan optimalisasi pelayanan publik.
    </p>

    <!-- B. MAKSUD DAN TUJUAN -->
    <div class="section-heading">B. MAKSUD DAN TUJUAN</div>
    <p class="indent">
        Dalam rangka memberikan kepastian berfungsinya mesin {{ $form['pekerjaan'] }} secara optimal serta mewujudkan ruang kerja yang nyaman untuk aktivitas kerja dalam kantor di lingkungan Badan Pusat Statistik Provinsi Gorontalo.
    </p>

    <!-- C. TARGET -->
    <div class="section-heading">C. TARGET</div>
    <p class="indent">
        Target kegiatan adalah berfungsinya seluruh peralatan dan mesin berupa mesin {{ $form['pekerjaan'] }} agar dapat digunakan oleh pegawai untuk kelancaran kinerja dan operasional perkantoran lainnya serta mengoptimalisasi pelayanan publik.
    </p>

    <!-- D. RUANG LINGKUP -->
    <div class="section-heading">D. RUANG LINGKUP</div>
    <p class="indent">
        Ruang lingkup kegiatan pemeliharaan {{ $form['pekerjaan'] }} dilingkungan Kantor BPS yaitu untuk tersedianya penyedia untuk melakukan pemeliharaan rutin, berkala. Pemeliharaan peralatan dan mesin yaitu perawatan (cuci dan perbaikan) serta penggantian <em>sparepart</em>.
    </p>

    <!-- E. PRODUK YANG DIHASILKAN -->
    <div class="section-heading">E. PRODUK YANG DIHASILKAN</div>
    <p class="indent">
        Terlaksananya pemeliharaan peralatan dan mesin berupa pemeliharaan {{ $form['pekerjaan'] }} dilingkungan Kantor BPS secara rutin, berkala.
    </p>

    <!-- F. WAKTU PELAKSANAAN -->
    <div class="section-heading">F. WAKTU PELAKSANAAN</div>
    <p class="indent">
        Pelaksanaan kegiatan dilaksanakan secara rutin dan secara berkala dengan tujuan tertentu misalnya terdapat indikasi penurunan kinerja/kerusakan ruangan kemudian diperbaiki.
    </p>
    <table class="detail-table">
        <tr>
            <td style="width: 180px; padding-left: 16px;">Pelaksanaan Pengadaan</td>
            <td style="width: 10px;">:</td>
            <td class="font-medium">{{ $form['waktu_pelaksanaan'] }}</td>
        </tr>
        <tr>
            <td style="padding-left: 16px;">Pelaksanaan Kontrak</td>
            <td>:</td>
            <td class="font-medium">{{ $form['waktu_pelaksanaan'] }}</td>
        </tr>
        <tr>
            <td style="padding-left: 16px;">Pemanfaatan Hasil</td>
            <td>:</td>
            <td class="font-medium">{{ $form['waktu_pelaksanaan'] }}</td>
        </tr>
    </table>

    <!-- ================= HALAMAN 2 ================= -->
    <div class="page-break"></div>

    <!-- G. SPESIFIKASI TEKNIS -->
    <div class="section-heading">G. SPESIFIKASI TEKNIS</div>
    <div style="padding-left: 16px;">
        <div class="spec-section">
            <p class="sub-heading">1. Teknis pekerjaan / Jenis Pekerjaan :</p>
            <div style="padding-left: 20px;">
                <p class="font-medium" style="font-size: 9.5pt;">a. Pemasangan:</p>
                <ul>
                    <li>Bongkar Pasang unit, menyambung pipa refrigeran, serta instalasi jalur kelistrikan AC</li>
                </ul>

                <p class="font-medium" style="font-size: 9.5pt;">b. Perawatan Rutin:</p>
                <ul>
                    <li>Mencuci filter dan evaporator, mengecek tekanan freon, serta memastikan aliran air pembuangan lancar.</li>
                </ul>

                <p class="font-medium" style="font-size: 9.5pt;">c. Perbaikan:</p>
                <ul>
                    <li>Menganalisis kerusakan, mengganti sparepart seperti kapasitor, modul PCB, kipas blower, atau memperbaiki kebocoran.</li>
                </ul>

                <p class="font-medium" style="font-size: 9.5pt;">d. Pengisian Freon:</p>
                <ul>
                    <li>Menambah atau mengisi ulang cairan pendingin jika tekanan atau ampere menunjukan angka di bawah normal.</li>
                </ul>
            </div>
        </div>

        <div class="spec-section">
            <p class="sub-heading">2. Jenis Kualitas Sparepart / Barang :</p>
            <div style="padding-left: 20px; font-size: 9.5pt;">
                <p>a. Original</p>
                <p>b. Non Pabrikan</p>
            </div>
        </div>
    </div>

    <!-- H. BIAYA -->
    <div class="section-heading">H. BIAYA</div>
    <p class="text-justify">
        Perkiraan total biaya untuk kegiatan {{ $form['pekerjaan'] }} sebanyak 
        <strong>{{ $form['total_unit'] }}</strong> ({{ $form['total_unit_terbilang'] }}) unit sebesar 
        <strong>Rp{{ number_format($form['total_biaya'], 0, ',', '.') }}.- ({{ $form['total_biaya_terbilang'] }})</strong>. Rincian biaya pelaksanaan kegiatan tersebut adalah sebagai berikut:
    </p>

    <table class="rab-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 40%;">Kegiatan</th>
                <th style="width: 10%;">Vol</th>
                <th style="width: 10%;">Satuan</th>
                <th style="width: 17%;">Harga Satuan</th>
                <th style="width: 18%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rabItems as $idx => $item)
            <tr>
                <td class="text-center">{{ $idx + 1 }}.</td>
                <td class="font-medium">{{ $item['kegiatan'] }}</td>
                <td class="text-center">{{ $item['volume'] }}</td>
                <td class="text-center">{{ $item['satuan'] }}</td>
                <td class="text-right">{{ number_format($item['harga_satuan'], 0, ',', '.') }}</td>
                <td class="text-right font-medium">{{ number_format($item['total'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td class="text-center">1.</td>
                <td class="font-medium">{{ $form['pekerjaan'] }}</td>
                <td class="text-center">{{ $form['total_unit'] }}</td>
                <td class="text-center">Unit</td>
                <td class="text-right">{{ number_format($form['total_biaya'], 0, ',', '.') }}</td>
                <td class="text-right font-medium">{{ number_format($form['total_biaya'], 0, ',', '.') }}</td>
            </tr>
            @endforelse
            <tr style="font-weight: bold; background-color: #f0f4f8;">
                <td colspan="5" class="text-left font-bold" style="padding: 6px; border: 1px solid #777777;">SUB TOTAL</td>
                <td class="text-right font-bold" style="padding: 6px; border: 1px solid #777777; text-decoration: underline;">Rp {{ number_format($form['total_biaya'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <table class="signature-table">
        <tr>
            <td colspan="2" style="text-align: center; font-weight: 600; padding-bottom: 10px;">
                {{ $form['kota'] }}, {{ $form['tanggal'] }}
            </td>
        </tr>
        <tr>
            <td style="text-align: center;">
                <div class="font-medium" style="margin-bottom: 4px;">Penanggung Jawab Kegiatan</div>
                <div class="sig-space"></div>
                <div class="font-bold" style="text-decoration: underline;">{{ $form['penanggung_jawab_nama'] }}</div>
                <div style="font-size: 9pt;">NIP. {{ $form['penanggung_jawab_nip'] }}</div>
            </td>
            <td style="text-align: center;">
                <div class="font-medium" style="margin-bottom: 4px;">Pejabat Pembuat Komitmen</div>
                <div class="sig-space"></div>
                <div class="font-bold" style="text-decoration: underline;">{{ $form['ppk_nama'] }}</div>
                <div style="font-size: 9pt;">NIP. {{ $form['ppk_nip'] }}</div>
            </td>
        </tr>
    </table>
@endif

</body>
</html>
