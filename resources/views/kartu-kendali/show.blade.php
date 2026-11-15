@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="kartuKendaliPdfDownloader()">
    <!-- Action Bar & Navigation (Hidden on Print) -->
    <div class="no-print bg-white/95 backdrop-blur-sm rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 sticky top-[68px] sm:top-[84px] z-20">
        <div class="flex items-center gap-3">
            <a href="{{ route('kartu-kendali.index') }}" 
               class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors"
               title="Kembali ke Daftar Kartu Kendali">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-slate-900">Kartu Kendali Pemeliharaan</h1>
                <p class="text-xs text-slate-500">Pratinjau dan cetak kartu kendali resmi aset BMN</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            <!-- Filter Tahun Anggaran -->
            <form method="GET" action="{{ route('kartu-kendali.show', $aset->id) }}" class="flex items-center gap-2">
                <label for="tahun" class="text-xs font-semibold text-slate-600 whitespace-nowrap">Tahun:</label>
                <select name="tahun" id="tahun" onchange="this.form.submit()" 
                        class="text-xs bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-slate-700 font-semibold focus:outline-hidden focus:ring-2 focus:ring-[#094886]">
                    <option value="semua" {{ $tahun == 'semua' ? 'selected' : '' }}>Semua Tahun</option>
                    @foreach($yearsAvailable as $yr)
                        <option value="{{ $yr }}" {{ $tahun == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </form>

            <!-- Tombol Unduh PDF with Processing Lock & Auto-Reset -->
            <button type="button" 
                    @click="downloadPdf()" 
                    :disabled="isDownloading"
                    :class="{
                        'bg-emerald-600 text-white cursor-default shadow-xs': isSuccess,
                        'bg-slate-400 text-white cursor-not-allowed': isDownloading,
                        'bg-[#094886] hover:bg-[#073868] text-white cursor-pointer shadow-xs hover:shadow': !isDownloading && !isSuccess
                    }"
                    class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-bold rounded-xl transition-all"
                    :title="isDownloading ? 'Sedang menyiapkan berkas PDF...' : 'Unduh langsung file PDF Kartu Kendali'">
                
                <template x-if="isDownloading">
                    <svg class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </template>

                <template x-if="isSuccess && !isDownloading">
                    <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </template>

                <template x-if="!isDownloading && !isSuccess">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                </template>

                <span x-text="downloadButtonText"></span>
            </button>

            <!-- Tombol Export Excel -->
            <a href="{{ route('kartu-kendali.export', [$aset->id, 'tahun' => $tahun]) }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-[#107c41] hover:bg-[#0c6233] text-white text-xs font-bold rounded-xl shadow-xs transition-all cursor-pointer">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="8" y1="13" x2="16" y2="17"></line>
                    <line x1="16" y1="13" x2="8" y2="17"></line>
                </svg>
                <span>Unduh Excel</span>
            </a>
        </div>
    </div>

    <!-- Paper Container matching official government template -->
    <div class="printable-area bg-white border border-slate-300/90 rounded-2xl shadow-sm p-6 md:p-10 mx-auto max-w-6xl overflow-x-auto text-black">
        <div class="min-w-[860px]">
            <!-- Header Logo & Institution (Centered) -->
            <div class="flex items-center justify-center gap-4 mb-3">
                <img src="{{ asset(file_exists(public_path('logo_KAK_opt.png')) ? 'logo_KAK_opt.png' : (file_exists(public_path('logo_KAK.png')) ? 'logo_KAK.png' : 'logo_bps.png')) }}" 
                     alt="Logo KAK" class="h-12 w-auto object-contain">
                <div class="text-xl md:text-2xl font-black italic tracking-tight text-slate-900 font-serif">
                    Badan Pusat Statistik Provinsi Gorontalo
                </div>
            </div>

            <!-- Title & Subtitle -->
            <div class="text-center my-4">
                <h2 class="text-base md:text-lg font-black tracking-wider uppercase">KARTU KENDALI</h2>
                <p class="text-sm md:text-base font-bold uppercase tracking-wide mt-0.5">
                    PEMELIHARAAN {{ strtoupper($aset->nama_barang ?? 'ASET BMN') }} TAHUN ANGGARAN {{ $tahun == 'semua' ? 'SELURUH TAHUN' : $tahun }}
                </p>
            </div>

            <!-- Asset Metadata (3 Columns) -->
            <div class="grid grid-cols-3 gap-6 text-[11px] md:text-[12px] font-medium leading-relaxed my-5">
                <!-- Col 1 -->
                <div class="space-y-1">
                    <div class="grid grid-cols-[100px_10px_1fr]">
                        <span class="text-slate-800">Nama Barang</span>
                        <span>:</span>
                        <span class="font-semibold">{{ $aset->nama_barang ?? '-' }}</span>
                    </div>
                    <div class="grid grid-cols-[100px_10px_1fr]">
                        <span class="text-slate-800">Kode Barang</span>
                        <span>:</span>
                        <span class="font-semibold">{{ $aset->clean_kode_barang ?? '-' }}</span>
                    </div>
                    <div class="grid grid-cols-[100px_10px_1fr]">
                        <span class="text-slate-800">Nomor Aset</span>
                        <span>:</span>
                        <span class="font-semibold">{{ $aset->clean_nup ?? '-' }}</span>
                    </div>
                </div>

                <!-- Col 2 -->
                <div class="space-y-1">
                    <div class="grid grid-cols-[110px_10px_1fr]">
                        <span class="text-slate-800">Merk/Tipe</span>
                        <span>:</span>
                        <span class="font-semibold">{{ trim(($aset->merk ?? '') . ' ' . ($aset->tipe ?? '')) ?: '-' }}</span>
                    </div>
                    <div class="grid grid-cols-[110px_10px_1fr]">
                        <span class="text-slate-800">Nomor Seri</span>
                        <span>:</span>
                        <span class="font-semibold">{{ $aset->no_sertifikat ?: ($aset->no_dokumen ?: '-') }}</span>
                    </div>
                    <div class="grid grid-cols-[110px_10px_1fr]">
                        <span class="text-slate-800">Tahun Perolehan</span>
                        <span>:</span>
                        <span class="font-semibold">{{ $aset->tanggal_perolehan ? $aset->tanggal_perolehan->format('Y') : '-' }}</span>
                    </div>
                </div>

                <!-- Col 3 -->
                <div class="space-y-1">
                    <div class="grid grid-cols-[130px_10px_1fr]">
                        <span class="text-slate-800">Lokasi BMN</span>
                        <span>:</span>
                        <span class="font-semibold">{{ $aset->alamat ?: 'Lantai 1' }}</span>
                    </div>
                    <div class="grid grid-cols-[130px_10px_1fr]">
                        <span class="text-slate-800">Ruangan</span>
                        <span>:</span>
                        <span class="font-semibold">{{ $aset->clean_lokasi_ruang ?? '-' }}</span>
                    </div>
                    <div class="grid grid-cols-[130px_10px_1fr]">
                        <span class="text-slate-800">Status Barang (B/RR)</span>
                        <span>:</span>
                        <span class="font-semibold">{{ $aset->kondisi ?? 'Baik' }}</span>
                    </div>
                </div>
            </div>

            <!-- Table (9 Columns Layout matching Excel exactly) -->
            <div class="mt-4 border border-black overflow-hidden">
                <table class="w-full border-collapse text-[11px] md:text-[11.5px]">
                    <thead>
                        <tr class="bg-white text-center font-bold">
                            <th rowspan="2" class="border border-black px-2 py-2 w-10">NO</th>
                            <th colspan="2" class="border border-black px-2 py-1.5">Form Permintaan Anggaran</th>
                            <th colspan="2" class="border border-black px-2 py-1.5">Tanggal Service Berkala</th>
                            <th colspan="2" class="border border-black px-2 py-1.5">Jenis Kegiatan</th>
                            <th rowspan="2" class="border border-black px-2 py-2 w-44">Bengkel yang mengerjakan</th>
                            <th rowspan="2" class="border border-black px-2 py-2 w-32">Biaya</th>
                        </tr>
                        <tr class="bg-white text-center font-bold">
                            <th class="border border-black px-2 py-1.5 w-44">Nomor</th>
                            <th class="border border-black px-2 py-1.5 w-28">Tanggal</th>
                            <th class="border border-black px-2 py-1.5 w-28">Mulai Pekerjaan</th>
                            <th class="border border-black px-2 py-1.5 w-28">Selesai Pekerjaan</th>
                            <th class="border border-black px-2 py-1.5">Jasa Service</th>
                            <th class="border border-black px-2 py-1.5">Sparepart yang di ganti</th>
                        </tr>
                        <!-- Numbering row (1) - (9) -->
                        <tr class="bg-slate-50 text-center text-[10px] font-semibold text-slate-700">
                            <th class="border border-black py-0.5">(1)</th>
                            <th class="border border-black py-0.5">(2)</th>
                            <th class="border border-black py-0.5">(3)</th>
                            <th class="border border-black py-0.5">(4)</th>
                            <th class="border border-black py-0.5">(5)</th>
                            <th class="border border-black py-0.5">(6)</th>
                            <th class="border border-black py-0.5">(7)</th>
                            <th class="border border-black py-0.5">(8)</th>
                            <th class="border border-black py-0.5">(9)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalRows = max(count($rows), 15);
                        @endphp
                        @for($i = 0; $i < $totalRows; $i++)
                            @php
                                $row = $rows[$i] ?? null;
                            @endphp
                            <tr class="hover:bg-slate-50/50">
                                <td class="border border-black text-center py-2 px-1 text-slate-800 font-medium">
                                    {{ $i + 1 }}
                                </td>
                                <td class="border border-black px-2 py-2 text-slate-900 font-mono text-[10px]">
                                    {{ $row ? $row['no_fpa'] : '' }}
                                </td>
                                <td class="border border-black text-center px-2 py-2 text-slate-800">
                                    {{ $row ? $row['tgl_fpa'] : '' }}
                                </td>
                                <td class="border border-black text-center px-2 py-2 text-slate-800">
                                    {{ $row ? $row['tgl_mulai'] : '' }}
                                </td>
                                <td class="border border-black text-center px-2 py-2 text-slate-800">
                                    {{ $row ? $row['tgl_selesai'] : '' }}
                                </td>
                                <td class="border border-black px-2 py-2 text-slate-800">
                                    {{ $row ? $row['jasa_service'] : '' }}
                                </td>
                                <td class="border border-black px-2 py-2 text-slate-800">
                                    {{ $row ? $row['sparepart'] : '' }}
                                </td>
                                <td class="border border-black px-2 py-2 text-slate-800">
                                    {{ $row ? $row['vendor'] : '' }}
                                </td>
                                <td class="border border-black text-right px-3 py-2 font-mono text-slate-900">
                                    @if($row && $row['biaya'] > 0)
                                        <div class="flex justify-between">
                                            <span>Rp</span>
                                            <span>{{ number_format($row['biaya'], 0, ',', '.') }}</span>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                    @if(count($rows) > 0)
                    <tfoot>
                        <tr class="font-bold bg-slate-50">
                            <td colspan="8" class="border border-black text-right px-4 py-2 uppercase tracking-wide text-[11px]">
                                Total Biaya Pemeliharaan:
                            </td>
                            <td class="border border-black text-right px-3 py-2 font-mono text-slate-900 text-xs">
                                <div class="flex justify-between font-bold">
                                    <span>Rp</span>
                                    <span>{{ number_format($totalBiaya, 0, ',', '.') }}</span>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            <!-- Bottom Notes / Signatures area if needed -->
            <div class="mt-8 flex justify-between items-start text-xs text-slate-700">
                <div class="italic text-[10px] text-slate-500">
                    Dicetak otomatis melalui Sistem Manajemen Inventaris BPS Gorontalo pada {{ now()->translatedFormat('d F Y H:i') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Stylesheet -->
<style>
@media print {
    /* Hide layout chrome */
    nav, aside, header, #app-sidebar, #app-header, .no-print, [role="navigation"] {
        display: none !important;
    }

    html, body {
        background-color: #ffffff !important;
        color: #000000 !important;
        margin: 0 !important;
        padding: 0 !important;
        height: auto !important;
        min-height: 0 !important;
        overflow: visible !important;
        display: block !important;
        font-family: Arial, Calibri, sans-serif !important;
    }

    #app-main-content, main, .overflow-x-auto, .overflow-y-auto {
        display: block !important;
        position: static !important;
        overflow: visible !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        height: auto !important;
    }

    .printable-area {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 auto !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    table {
        border-collapse: collapse !important;
        width: 100% !important;
    }

    th, td {
        border: 1px solid #000000 !important;
        color: #000000 !important;
    }

    @page {
        size: A4 landscape;
        margin: 10mm;
    }
}
</style>

<script>
function kartuKendaliPdfDownloader() {
    return {
        isDownloading: false,
        isSuccess: false,
        downloadButtonText: 'Unduh PDF',
        downloadPdf() {
            if (this.isDownloading) return;

            this.isDownloading = true;
            this.isSuccess = false;
            this.downloadButtonText = 'Menyiapkan PDF...';

            fetch('{{ route('kartu-kendali.pdf', [$aset->id, 'tahun' => $tahun, 'download' => 1]) }}')
                .then(res => {
                    if (!res.ok) throw new Error('Gagal memproses berkas PDF.');
                    return res.blob();
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    const safeNup = '{{ preg_replace('/[^a-zA-Z0-9_-]/', '_', $aset->clean_nup ?: $aset->id) }}';
                    a.download = `Kartu-Kendali-${safeNup}-{{ $tahun }}.pdf`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);

                    // Berikan feedback sejenak, lalu pulihkan tombol agar bisa ditekan lagi
                    this.isDownloading = false;
                    this.isSuccess = true;
                    this.downloadButtonText = '✓ Selesai Terunduh';

                    setTimeout(() => {
                        this.isSuccess = false;
                        this.downloadButtonText = 'Unduh PDF';
                    }, 2000);
                })
                .catch(err => {
                    console.error(err);
                    this.isDownloading = false;
                    this.isSuccess = false;
                    this.downloadButtonText = 'Unduh PDF';
                    alert('Gagal mengunduh berkas PDF Kartu Kendali. Silakan coba kembali.');
                });
        }
    };
}
</script>
@endsection
