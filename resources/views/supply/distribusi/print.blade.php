<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak BAST - {{ $bast->nomor_bast }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #000;
            background: #fff;
            padding: 2.5cm 2cm 2cm 2.5cm;
            line-height: 1.45;
        }
        .header {
            display: flex;
            align-items: center;
            border-bottom: 2.5px solid #000;
            padding-bottom: 10px;
            margin-bottom: 18px;
        }
        .header img {
            width: 75px;
            height: auto;
            margin-right: 20px;
        }
        .header-text {
            flex: 1;
            text-align: center;
        }
        .header-text h3 {
            font-size: 12pt;
            font-weight: normal;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }
        .header-text h2 {
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .header-text p {
            font-size: 9pt;
            color: #333;
        }
        .title-block {
            text-align: center;
            margin-bottom: 18px;
        }
        .title-block h1 {
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .title-block p {
            font-size: 10.5pt;
            font-weight: bold;
        }
        .paragraph {
            text-align: justify;
            margin-bottom: 12px;
            font-size: 11pt;
        }
        .identity-table {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: collapse;
            font-size: 11pt;
        }
        .identity-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin: 14px 0 16px 0;
            font-size: 10.5pt;
        }
        .item-table th, .item-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: middle;
        }
        .item-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .signatures {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 11pt;
        }
        .sig-space {
            height: 75px;
        }
        .sig-name {
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .no-print-bar {
            position: fixed;
            top: 15px;
            right: 15px;
            background: #094886;
            color: #fff;
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            font-family: sans-serif;
            font-size: 13px;
            font-weight: bold;
            border: none;
            z-index: 9999;
        }
        .no-print-bar:hover {
            background: #073868;
        }
        @media print {
            .no-print-bar {
                display: none !important;
            }
            body {
                padding: 0;
            }
            @page {
                size: A4 portrait;
                margin: 2cm 2cm 2cm 2.5cm;
            }
        }
    </style>
</head>
<body>

    <button type="button" class="no-print-bar" onclick="window.print()">
        🖨️ Cetak Dokumen (Print)
    </button>

    <!-- KOP SURAT BPS -->
    <div class="header">
        <img src="{{ asset('logo_bps.png') }}" alt="Logo BPS">
        <div class="header-text">
            <h3>BADAN PUSAT STATISTIK</h3>
            <h2>PROVINSI GORONTALO</h2>
            <p>Jl. Durian No. 40, Kel. Tomulobutao, Kec. Dungingi, Kota Gorontalo, Gorontalo 96136</p>
        </div>
    </div>

    <!-- JUDUL -->
    <div class="title-block">
        <h1>BERITA ACARA SERAH TERIMA BARANG PERSEDIAAN</h1>
        <p>Nomor: {{ $bast->nomor_bast }}</p>
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
            <td style="width: 25px;">1.</td>
            <td style="width: 140px;">Nama</td>
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
            <td colspan="3" style="padding-bottom: 10px; font-style: italic;">
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
            <td colspan="3" style="font-style: italic;">
                Selanjutnya disebut sebagai <strong>PIHAK KEDUA</strong> (Yang Menerima).
            </td>
        </tr>
    </table>

    <!-- KLAUSUL -->
    <div class="paragraph">
        PIHAK PERTAMA menyerahkan barang persediaan kepada PIHAK KEDUA, dan PIHAK KEDUA menyatakan telah menerima barang persediaan tersebut dalam keadaan baik, cukup, dan lengkap untuk keperluan: 
        <strong>{{ $bast->kegiatan_keperluan ?: 'Operasional Kantor' }}</strong>, dengan rincian barang sebagai berikut:
    </div>

    <!-- TABEL BARANG -->
    <table class="item-table">
        <thead>
            <tr>
                <th style="width: 35px;">NO</th>
                <th>NAMA BARANG PERSEDIAAN</th>
                <th style="width: 140px;">SPESIFIKASI</th>
                <th style="width: 75px;">JUMLAH</th>
                <th style="width: 75px;">SATUAN</th>
                <th style="width: 120px;">KETERANGAN</th>
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
                <td>{{ $it->keterangan ?: 'Baik' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- PENUTUP -->
    <div class="paragraph">
        Demikian Berita Acara Serah Terima barang persediaan ini dibuat dengan sebenarnya dalam rangkap secukupnya untuk dapat dipergunakan sebagaimana mestinya.
    </div>

    <!-- TANDA TANGAN -->
    <table class="signatures">
        <tr>
            <td>
                Yang Menerima,<br>
                <strong>PIHAK KEDUA</strong>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $bast->pihak_kedua_nama }}</div>
                @if($bast->pihak_kedua_nip)
                    <div>NIP. {{ $bast->pihak_kedua_nip }}</div>
                @endif
                <div>{{ $bast->pihak_kedua_unit_kerja }}</div>
            </td>
            <td>
                Yang Menyerahkan,<br>
                <strong>PIHAK PERTAMA</strong>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $bast->pihak_pertama_nama }}</div>
                @if($bast->pihak_pertama_nip)
                    <div>NIP. {{ $bast->pihak_pertama_nip }}</div>
                @endif
                <div>{{ $bast->pihak_pertama_jabatan }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
