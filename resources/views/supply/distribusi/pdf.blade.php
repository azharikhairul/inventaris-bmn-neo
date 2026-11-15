<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>BAST-{{ $bast->nomor_bast }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 2cm 2cm 2cm 2.5cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #111;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo-td {
            width: 70px;
        }
        .logo-img {
            width: 65px;
            height: auto;
        }
        .header-text {
            text-align: center;
        }
        .header-text .title-kem {
            font-size: 10pt;
            letter-spacing: 1px;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .header-text .title-bps {
            font-size: 13pt;
            font-weight: bold;
            margin: 2px 0;
            color: #000;
        }
        .header-text .address {
            font-size: 8pt;
            color: #444;
            margin: 0;
        }
        .title-section {
            text-align: center;
            margin-bottom: 14px;
        }
        .title-section h1 {
            font-size: 11pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0 0 3px 0;
            letter-spacing: 0.5px;
        }
        .title-section .nomor {
            font-size: 9.5pt;
            font-weight: bold;
            margin: 0;
        }
        .paragraph {
            text-align: justify;
            margin-bottom: 10px;
            font-size: 9.5pt;
        }
        .identity-table {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
            font-size: 9.5pt;
        }
        .identity-table td {
            padding: 2px 3px;
            vertical-align: top;
        }
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0 14px 0;
            font-size: 9pt;
        }
        .item-table th, .item-table td {
            border: 1px solid #222;
            padding: 5px 6px;
            vertical-align: middle;
        }
        .item-table th {
            background-color: #f2f4f8;
            text-align: center;
            font-weight: bold;
        }
        .signatures {
            width: 100%;
            margin-top: 24px;
            border-collapse: collapse;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 9.5pt;
        }
        .sig-space {
            height: 65px;
        }
        .sig-name {
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <!-- KOP RESMI -->
    <table class="header-table">
        <tr>
            @if($logoBase64)
            <td class="logo-td">
                <img src="{{ $logoBase64 }}" class="logo-img" alt="Logo">
            </td>
            @endif
            <td class="header-text">
                <div class="title-kem">BADAN PUSAT STATISTIK</div>
                <div class="title-bps">PROVINSI GORONTALO</div>
                <div class="address">Jl. Durian No. 40, Kel. Tomulobutao, Kec. Dungingi, Kota Gorontalo, Gorontalo 96136</div>
            </td>
        </tr>
    </table>

    <!-- JUDUL -->
    <div class="title-section">
        <h1>BERITA ACARA SERAH TERIMA BARANG PERSEDIAAN</h1>
        <div class="nomor">Nomor: {{ $bast->nomor_bast }}</div>
    </div>

    <!-- PEMBUKA -->
    <div class="paragraph">
        Pada hari ini, <strong>{{ $bast->tanggal_bast ? $bast->tanggal_bast->translatedFormat('l') : '...' }}</strong>, 
        tanggal <strong>{{ $bast->tanggal_bast ? $bast->tanggal_bast->translatedFormat('d') : '...' }}</strong>, 
        bulan <strong>{{ $bast->tanggal_bast ? $bast->tanggal_bast->translatedFormat('F') : '...' }}</strong>, 
        tahun <strong>{{ $bast->tanggal_bast ? $bast->tanggal_bast->translatedFormat('Y') : '...' }}</strong>, 
        yang bertanda tangan di bawah ini:
    </div>

    <!-- DATA PIHAK PERTAMA & KEDUA -->
    <table class="identity-table">
        <tr>
            <td style="width: 20px;">1.</td>
            <td style="width: 130px;">Nama</td>
            <td style="width: 10px;">:</td>
            <td><strong>{{ $bast->pihak_pertama_nama }}</strong></td>
        </tr>
        <tr>
            <td></td>
            <td>NIP</td>
            <td>:</td>
            <td>{{ $bast->pihak_pertama_nip ?: '-' }}</td>
        </tr>
        <tr>
            <td></td>
            <td>Jabatan</td>
            <td>:</td>
            <td>{{ $bast->pihak_pertama_jabatan }}</td>
        </tr>
        <tr>
            <td></td>
            <td colspan="3" style="padding-bottom: 8px; font-style: italic; color: #444;">
                Selanjutnya disebut sebagai <strong>PIHAK PERTAMA</strong> (Yang Menyerahkan).
            </td>
        </tr>
        <tr>
            <td>2.</td>
            <td>Nama</td>
            <td>:</td>
            <td><strong>{{ $bast->pihak_kedua_nama }}</strong></td>
        </tr>
        <tr>
            <td></td>
            <td>NIP</td>
            <td>:</td>
            <td>{{ $bast->pihak_kedua_nip ?: '-' }}</td>
        </tr>
        <tr>
            <td></td>
            <td>Unit Kerja / Tim</td>
            <td>:</td>
            <td>{{ $bast->pihak_kedua_unit_kerja }}</td>
        </tr>
        <tr>
            <td></td>
            <td colspan="3" style="font-style: italic; color: #444;">
                Selanjutnya disebut sebagai <strong>PIHAK KEDUA</strong> (Yang Menerima).
            </td>
        </tr>
    </table>

    <!-- KLAUSUL -->
    <div class="paragraph">
        PIHAK PERTAMA menyerahkan barang persediaan kepada PIHAK KEDUA, dan PIHAK KEDUA menyatakan telah menerima barang persediaan tersebut dalam keadaan baik, cukup, dan lengkap untuk keperluan: 
        <strong>{{ $bast->kegiatan_keperluan ?: 'Operasional Kantor' }}</strong>, dengan rincian barang sebagai berikut:
    </div>

    <!-- TABEL RINCIAN BARANG -->
    <table class="item-table">
        <thead>
            <tr>
                <th style="width: 25px;">NO</th>
                <th>NAMA BARANG PERSEDIAAN</th>
                <th style="width: 120px;">SPESIFIKASI</th>
                <th style="width: 60px;">JUMLAH</th>
                <th style="width: 60px;">SATUAN</th>
                <th style="width: 100px;">KETERANGAN</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bast->items as $idx => $it)
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td><strong>{{ $it->nama_barang }}</strong></td>
                <td>{{ $it->spesifikasi ?: '-' }}</td>
                <td style="text-align: center; font-weight: bold;">{{ $it->jumlah }}</td>
                <td style="text-align: center;">{{ $it->satuan }}</td>
                <td>{{ $it->keterangan ?: 'Baik & Lengkap' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- PENUTUP -->
    <div class="paragraph">
        Demikian Berita Acara Serah Terima barang persediaan ini dibuat dengan sebenarnya dalam rangkap secukupnya untuk dapat dipergunakan sebagaimana mestinya.
    </div>

    <!-- TANDA TANGAN DUA PIHAK -->
    <table class="signatures">
        <tr>
            <td>
                Yang Menerima,<br>
                <strong>PIHAK KEDUA</strong>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $bast->pihak_kedua_nama }}</div>
                @if($bast->pihak_kedua_nip)
                    <div style="font-size: 8.5pt;">NIP. {{ $bast->pihak_kedua_nip }}</div>
                @endif
                <div style="font-size: 8.5pt; color: #555;">{{ $bast->pihak_kedua_unit_kerja }}</div>
            </td>
            <td>
                Yang Menyerahkan,<br>
                <strong>PIHAK PERTAMA</strong>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $bast->pihak_pertama_nama }}</div>
                @if($bast->pihak_pertama_nip)
                    <div style="font-size: 8.5pt;">NIP. {{ $bast->pihak_pertama_nip }}</div>
                @endif
                <div style="font-size: 8.5pt; color: #555;">{{ $bast->pihak_pertama_jabatan }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
