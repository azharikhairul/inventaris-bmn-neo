<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Kartu Kendali - {{ $aset->nama_barang ?? 'Aset' }} (NUP {{ $aset->clean_nup ?? '-' }})</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 12mm 8mm 12mm;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8pt;
            line-height: 1.3;
            color: #000000;
            margin: 0;
            padding: 0;
        }

        .header-table {
            width: 100%;
            margin-bottom: 6px;
        }

        .header-table td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }

        .logo-img {
            height: 38px;
            width: auto;
        }

        .agency-title {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 14pt;
            font-weight: bold;
            font-style: italic;
            color: #000000;
            padding-left: 10px;
        }

        .doc-title-section {
            text-align: center;
            margin: 6px 0 10px 0;
        }

        .doc-title-section h1 {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 2px 0;
        }

        .doc-title-section h2 {
            font-size: 9.5pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
            color: #111111;
        }

        /* Metadata Table */
        .meta-table {
            width: 100%;
            margin-bottom: 8px;
            font-size: 7.5pt;
            border-collapse: collapse;
        }

        .meta-table td {
            vertical-align: top;
            padding: 1px 3px;
            border: none;
        }

        .meta-label {
            color: #333333;
            width: 85px;
        }

        .meta-colon {
            width: 8px;
            text-align: center;
        }

        .meta-val {
            font-weight: bold;
            color: #000000;
        }

        /* Maintenance History Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
        }

        .data-table th {
            background-color: #ffffff;
            color: #000000;
            font-weight: bold;
            padding: 3px 2px;
            border: 1px solid #000000;
            text-align: center;
            vertical-align: middle;
        }

        .data-table td {
            border: 1px solid #000000;
            padding: 3px 3px;
            vertical-align: top;
            color: #000000;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }

        .numbering-row th {
            font-weight: normal;
            font-size: 6.5pt;
            background-color: #f8f9fa;
            padding: 1px 0;
        }

        .total-row td {
            background-color: #f0f4f8;
            font-weight: bold;
            font-size: 7.5pt;
        }

        .footer-note {
            margin-top: 8px;
            font-size: 6.5pt;
            color: #555555;
            font-style: italic;
        }
    </style>
</head>
<body>

    <!-- Header Logo & Agency (Centered) -->
    <table class="header-table">
        <tr>
            <td style="text-align: center;">
                <table style="margin: 0 auto; border: none;">
                    <tr>
                        <td style="border: none; vertical-align: middle; padding-right: 10px;">
                            @if(!empty($logoBase64))
                                <img src="{{ $logoBase64 }}" alt="Logo KAK" class="logo-img">
                            @elseif(file_exists(public_path('logo_KAK_opt.png')))
                                <img src="{{ public_path('logo_KAK_opt.png') }}" alt="Logo KAK" class="logo-img">
                            @elseif(file_exists(public_path('logo_KAK.png')))
                                <img src="{{ public_path('logo_KAK.png') }}" alt="Logo KAK" class="logo-img">
                            @elseif(file_exists(public_path('logo_bps.png')))
                                <img src="{{ public_path('logo_bps.png') }}" alt="Logo BPS" class="logo-img">
                            @endif
                        </td>
                        <td style="border: none; vertical-align: middle;">
                            <div class="agency-title">Badan Pusat Statistik Provinsi Gorontalo</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Title & Subtitle -->
    <div class="doc-title-section">
        <h1>KARTU KENDALI</h1>
        <h2>PEMELIHARAAN {{ strtoupper($aset->nama_barang ?? 'ASET BMN') }} TAHUN ANGGARAN {{ $tahun == 'semua' ? 'SELURUH TAHUN' : $tahun }}</h2>
    </div>

    <!-- Metadata Block (3 Columns matching official sheet) -->
    <table class="meta-table">
        <tr>
            <!-- Column 1 -->
            <td style="width: 33%;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td class="meta-label">Nama Barang</td>
                        <td class="meta-colon">:</td>
                        <td class="meta-val">{{ $aset->nama_barang ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Kode Barang</td>
                        <td class="meta-colon">:</td>
                        <td class="meta-val">{{ $aset->clean_kode_barang ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Nomor Aset</td>
                        <td class="meta-colon">:</td>
                        <td class="meta-val">{{ $aset->clean_nup ?? '-' }}</td>
                    </tr>
                </table>
            </td>

            <!-- Column 2 -->
            <td style="width: 34%;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td class="meta-label">Merk/Tipe</td>
                        <td class="meta-colon">:</td>
                        <td class="meta-val">{{ trim(($aset->merk ?? '') . ' ' . ($aset->tipe ?? '')) ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Nomor Seri</td>
                        <td class="meta-colon">:</td>
                        <td class="meta-val">{{ $aset->no_sertifikat ?: ($aset->no_dokumen ?: '-') }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Tahun Perolehan</td>
                        <td class="meta-colon">:</td>
                        <td class="meta-val">{{ $aset->tanggal_perolehan ? $aset->tanggal_perolehan->format('Y') : '-' }}</td>
                    </tr>
                </table>
            </td>

            <!-- Column 3 -->
            <td style="width: 33%;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td class="meta-label">Lokasi BMN</td>
                        <td class="meta-colon">:</td>
                        <td class="meta-val">{{ $aset->alamat ?: 'Lantai 1' }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Ruangan</td>
                        <td class="meta-colon">:</td>
                        <td class="meta-val">{{ $aset->clean_lokasi_ruang ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Status Barang (B/RR)</td>
                        <td class="meta-colon">:</td>
                        <td class="meta-val">{{ $aset->kondisi ?? 'Baik' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Table of Maintenance History (Official 9 Columns Layout) -->
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 3%;">NO</th>
                <th colspan="2" style="width: 25%;">Form Permintaan Anggaran</th>
                <th colspan="2" style="width: 18%;">Tanggal Service Berkala</th>
                <th colspan="2" style="width: 26%;">Jenis Kegiatan</th>
                <th rowspan="2" style="width: 16%;">Bengkel yang mengerjakan</th>
                <th rowspan="2" style="width: 12%;">Biaya (Rp)</th>
            </tr>
            <tr>
                <th style="width: 15%;">Nomor</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 9%;">Mulai Pekerjaan</th>
                <th style="width: 9%;">Selesai Pekerjaan</th>
                <th style="width: 13%;">Jasa Service</th>
                <th style="width: 13%;">Sparepart yang di ganti</th>
            </tr>
            <tr class="numbering-row">
                <th>(1)</th>
                <th>(2)</th>
                <th>(3)</th>
                <th>(4)</th>
                <th>(5)</th>
                <th>(6)</th>
                <th>(7)</th>
                <th>(8)</th>
                <th>(9)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalRows = max(count($rows), 10);
            @endphp
            @for($i = 0; $i < $totalRows; $i++)
                @php
                    $row = $rows[$i] ?? null;
                @endphp
                <tr>
                    <td class="text-center font-bold">{{ $i + 1 }}</td>
                    <td>{{ $row ? $row['no_fpa'] : '' }}</td>
                    <td class="text-center">{{ $row ? $row['tgl_fpa'] : '' }}</td>
                    <td class="text-center">{{ $row ? $row['tgl_mulai'] : '' }}</td>
                    <td class="text-center">{{ $row ? $row['tgl_selesai'] : '' }}</td>
                    <td>{{ $row ? $row['jasa_service'] : '' }}</td>
                    <td>{{ $row ? $row['sparepart'] : '' }}</td>
                    <td>{{ $row ? $row['vendor'] : '' }}</td>
                    <td class="text-right font-bold">
                        @if($row && $row['biaya'] > 0)
                            {{ number_format($row['biaya'], 0, ',', '.') }}
                        @endif
                    </td>
                </tr>
            @endfor

            <!-- Total Row -->
            @if(count($rows) > 0)
            <tr class="total-row">
                <td colspan="8" class="text-right font-bold">TOTAL BIAYA PEMELIHARAAN:</td>
                <td class="text-right font-bold">{{ number_format($totalBiaya ?? 0, 0, ',', '.') }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- Footer Note -->
    <div class="footer-note">
        Dicetak otomatis melalui Sistem Manajemen Inventaris BPS Provinsi Gorontalo pada {{ now()->translatedFormat('d F Y H:i') }}.
    </div>

</body>
</html>
