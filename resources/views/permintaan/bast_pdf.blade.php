<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>BAST - {{ $form['nomor_bast'] ?? 'Berita Acara Serah Terima' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 18mm 20mm 18mm 20mm;
        }
        
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10pt;
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

        .kop-table {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 18px;
        }

        .kop-table td {
            vertical-align: middle;
        }

        .kop-logo {
            width: 65px;
            text-align: left;
        }

        .kop-logo img {
            width: 60px;
            height: auto;
        }

        .kop-text {
            text-align: center;
        }

        .kop-text .instansi-kem {
            font-size: 10pt;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 0;
            text-transform: uppercase;
        }

        .kop-text .instansi-bps {
            font-size: 13pt;
            font-weight: bold;
            margin: 2px 0;
            text-transform: uppercase;
        }

        .kop-text .alamat {
            font-size: 8pt;
            color: #333;
            margin: 0;
        }

        .doc-title {
            text-align: center;
            margin-bottom: 16px;
        }

        .doc-title h1 {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
            text-decoration: underline;
        }

        .doc-title .nomor {
            font-size: 9.5pt;
            margin-top: 3px;
            font-weight: bold;
        }

        p {
            margin: 0 0 8px 0;
            text-align: justify;
        }

        .pihak-table {
            width: 100%;
            margin-bottom: 8px;
            margin-left: 8px;
            font-size: 9.5pt;
        }

        .pihak-table td {
            padding: 1.5px 0;
            vertical-align: top;
        }

        .job-box {
            width: 100%;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
            padding: 8px 12px;
            margin-bottom: 12px;
            font-size: 9.5pt;
        }

        .job-box td {
            padding: 2px 0;
            vertical-align: top;
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 14px;
            font-size: 9pt;
        }

        .item-table th, .item-table td {
            border: 1px solid #000;
            padding: 5px 6px;
        }

        .item-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }

        .ttd-table {
            width: 100%;
            margin-top: 14px;
            text-align: center;
            font-size: 9.5pt;
        }

        .ttd-table td {
            vertical-align: top;
        }

        .ttd-space {
            height: 60px;
        }
    </style>
</head>
<body>

    <!-- KOP Surat -->
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" alt="Logo BPS">
                @endif
            </td>
            <td class="kop-text">
                <div class="instansi-kem">BADAN PUSAT STATISTIK</div>
                <div class="instansi-bps">{{ $form['instansi'] ?? 'PROVINSI GORONTALO' }}</div>
                <div class="alamat">Jl. Dr. H. Farouk D. Isa No. 25, Kota Gorontalo 96115 • Telp: (0435) 822870 • Email: bps7500@bps.go.id</div>
            </td>
        </tr>
    </table>

    <!-- Judul Dokumen -->
    <div class="doc-title">
        <h1>BERITA ACARA SERAH TERIMA HASIL PEKERJAAN</h1>
        <div class="nomor">Nomor: {{ $form['nomor_bast'] ?? '-' }}</div>
    </div>

    <!-- Paragraf Pembuka -->
    <p>
        {{ $form['hari_tanggal'] ?? 'Pada hari ini' }}, bertempat di Kantor {{ $form['instansi'] ?? 'BPS Provinsi Gorontalo' }}, kami yang bertanda tangan di bawah ini:
    </p>

    <!-- Pihak Pertama -->
    <table class="pihak-table">
        <tr>
            <td style="width: 20px;">1.</td>
            <td style="width: 130px; font-weight: bold;">Nama</td>
            <td style="width: 12px;">:</td>
            <td style="font-weight: bold;">{{ $form['pihak_pertama_nama'] ?? '-' }}</td>
        </tr>
        <tr>
            <td></td>
            <td>Jabatan</td>
            <td>:</td>
            <td>{{ $form['pihak_pertama_jabatan'] ?? '-' }}</td>
        </tr>
        <tr>
            <td></td>
            <td>Instansi / Rekanan</td>
            <td>:</td>
            <td>{{ $form['pihak_pertama_perusahaan'] ?? '-' }}</td>
        </tr>
        <tr>
            <td></td>
            <td colspan="3" style="font-style: italic; color: #555;">
                Selanjutnya disebut sebagai <strong>PIHAK PERTAMA (Yang Menyerahkan)</strong>.
            </td>
        </tr>
    </table>

    <!-- Pihak Kedua -->
    <table class="pihak-table">
        <tr>
            <td style="width: 20px;">2.</td>
            <td style="width: 130px; font-weight: bold;">Nama</td>
            <td style="width: 12px;">:</td>
            <td style="font-weight: bold;">{{ $form['pihak_kedua_nama'] ?? '-' }}</td>
        </tr>
        <tr>
            <td></td>
            <td>NIP</td>
            <td>:</td>
            <td>{{ $form['pihak_kedua_nip'] ?? '-' }}</td>
        </tr>
        <tr>
            <td></td>
            <td>Jabatan</td>
            <td>:</td>
            <td>{{ $form['pihak_kedua_jabatan'] ?? 'Pejabat Pembuat Komitmen (PPK)' }}</td>
        </tr>
        <tr>
            <td></td>
            <td colspan="3" style="font-style: italic; color: #555;">
                Selanjutnya disebut sebagai <strong>PIHAK KEDUA (Yang Menerima)</strong>.
            </td>
        </tr>
    </table>

    <!-- Keterangan Pekerjaan -->
    <p>
        Kedua belah pihak telah bersepakat mengadakan serah terima hasil pekerjaan untuk kegiatan pengadaan/pemeliharaan dengan rincian sebagai berikut:
    </p>

    <table class="job-box">
        <tr>
            <td style="width: 140px; font-weight: bold;">Nama Pekerjaan</td>
            <td style="width: 12px;">:</td>
            <td style="font-weight: bold;">{{ $form['pekerjaan'] ?? '-' }}</td>
        </tr>
        <tr>
            <td>Jenis Pekerjaan</td>
            <td>:</td>
            <td>{{ $form['jenis'] ?? 'Pemeliharaan / Pengadaan' }}</td>
        </tr>
        <tr>
            <td>Lokasi Penyerahan</td>
            <td>:</td>
            <td>Kantor BPS Provinsi Gorontalo</td>
        </tr>
    </table>

    <!-- Tabel Rincian Barang / Hasil Pekerjaan -->
    <table class="item-table">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Uraian Barang / Pekerjaan</th>
                <th style="width: 60px;">Volume</th>
                <th style="width: 60px;">Satuan</th>
                <th style="width: 130px;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rabItems as $idx => $it)
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td style="font-weight: bold;">{{ $it['uraian'] ?? '-' }}</td>
                <td style="text-align: center;">{{ $it['volume'] ?? 1 }}</td>
                <td style="text-align: center;">{{ $it['satuan'] ?? 'unit' }}</td>
                <td style="text-align: center; color: #15803d;">{{ $it['keterangan'] ?? 'Kondisi Baik & Lengkap' }}</td>
            </tr>
            @empty
            <tr>
                <td style="text-align: center;">1</td>
                <td style="font-weight: bold;">{{ $form['pekerjaan'] ?? 'Hasil Pekerjaan' }}</td>
                <td style="text-align: center;">1</td>
                <td style="text-align: center;">paket</td>
                <td style="text-align: center;">Kondisi Baik & Lengkap</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Paragraf Penutup -->
    <p>
        PIHAK KEDUA telah memeriksa dengan seksama dan menerima hasil pekerjaan/barang yang diserahkan oleh PIHAK PERTAMA dalam keadaan baik, cukup, dan berfungsi sebagaimana mestinya sesuai ketentuan spesifikasi teknis yang dipersyaratkan.
    </p>

    <!-- Tanda Tangan -->
    <div style="margin-top: 15px;">
        <div style="text-align: right; font-size: 9.5pt; margin-bottom: 8px;">
            {{ $form['kota'] ?? 'Gorontalo' }}, {{ $form['tanggal'] ?? date('d F Y') }}
        </div>

        <table class="ttd-table">
            <tr>
                <td style="width: 50%;">
                    <div style="font-weight: bold;">PIHAK PERTAMA</div>
                    <div style="color: #444;">Yang Menyerahkan,</div>
                    <div class="ttd-space"></div>
                    <div style="font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                        {{ $form['pihak_pertama_nama'] ?? '-' }}
                    </div>
                    <div style="color: #444;">{{ $form['pihak_pertama_jabatan'] ?? '-' }}</div>
                </td>
                <td style="width: 50%;">
                    <div style="font-weight: bold;">PIHAK KEDUA</div>
                    <div style="color: #444;">Yang Menerima,</div>
                    <div class="ttd-space"></div>
                    <div style="font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                        {{ $form['pihak_kedua_nama'] ?? '-' }}
                    </div>
                    <div style="color: #444;">NIP. {{ $form['pihak_kedua_nip'] ?? '-' }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding-top: 18px;">
                    <div style="font-weight: bold;">Mengetahui,</div>
                    <div style="color: #444;">{{ $form['kpa_jabatan'] ?? 'Kuasa Pengguna Anggaran (KPA)' }}</div>
                    <div class="ttd-space" style="height: 55px;"></div>
                    <div style="font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                        {{ $form['kpa_nama'] ?? '-' }}
                    </div>
                    <div style="color: #444;">NIP. {{ $form['kpa_nip'] ?? '-' }}</div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>

