@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 pb-20">
    <!-- Header Title -->
    <div>
        <div class="flex items-center gap-2.5">
            <h1 class="text-2xl sm:text-[26px] font-bold text-slate-900 tracking-tight">
                {{ ($realisasi['jenis'] ?? '') === 'Supply' ? 'Detail Realisasi Supply (Persediaan)' : 'Detail Realisasi Perbaikan' }}
            </h1>
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold {{ ($realisasi['jenis'] ?? '') === 'Supply' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-[#094886] border border-blue-200' }}">
                {{ $realisasi['jenis'] ?? 'Maintenance' }}
            </span>
        </div>
        <p class="text-sm text-slate-500 mt-1">
            {{ ($realisasi['jenis'] ?? '') === 'Supply' ? 'Tinjau rincian pengadaan barang persediaan dan anggaran yang telah diselesaikan.' : 'Tinjau rincian pekerjaan dan anggaran yang telah diselesaikan.' }}
        </p>
    </div>

    <!-- Top Summary & Meta Cards -->
    <div class="bg-white rounded-xl border border-slate-200/90 p-6 shadow-xs space-y-6">
        <!-- Grid Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- No Dokumen FPA -->
            <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-4">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">NO. DOKUMEN FPA</span>
                <span class="block text-sm font-bold text-slate-900 mt-1 font-mono">{{ $realisasi['no_fpa'] ?? '-' }}</span>
            </div>

            <!-- Vendor Pelaksana -->
            <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-4">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">VENDOR PELAKSANA</span>
                <div class="flex items-center gap-1.5 mt-1">
                    <svg class="w-4 h-4 text-slate-500 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 7v14M21 7v14M6 11h2M6 15h2M16 11h2M16 15h2M9 21v-4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v4M9 3h6v4H9z"></path></svg>
                    <span class="text-sm font-bold text-slate-900 truncate">{{ $realisasi['vendor'] ?? '-' }}</span>
                </div>
            </div>

            <!-- Tanggal Pengerjaan -->
            <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-4">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">TANGGAL PENGERJAAN</span>
                <div class="mt-1">
                    @if(!empty($realisasi['tanggal_selesai']))
                        <span class="block text-sm font-bold text-slate-900">{{ $realisasi['tanggal_mulai'] }} - {{ $realisasi['tanggal_selesai'] }}</span>
                    @else
                        <span class="block text-sm font-bold text-slate-900">{{ $realisasi['tanggal_mulai'] }} <span class="text-xs text-amber-600 font-medium">(Sedang Proses)</span></span>
                    @endif
                </div>
            </div>

            <!-- Total Biaya Realisasi -->
            <div class="bg-blue-50/60 border border-blue-200/80 rounded-xl p-4">
                <span class="block text-[10px] font-bold text-blue-600 uppercase tracking-wider">TOTAL BIAYA REALISASI</span>
                <span class="block text-base font-extrabold text-[#0c4a82] mt-0.5">Rp {{ number_format($realisasi['total_realisasi'] ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Row 2 Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Pekerjaan / Kegiatan -->
            <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-4 sm:col-span-2">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">PEKERJAAN/KEGIATAN</span>
                <span class="block text-sm font-bold text-slate-900 mt-1">{{ $realisasi['pekerjaan'] ?? '-' }}</span>
            </div>

            <!-- RAB Permintaan -->
            <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-4 sm:col-span-2">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">RAB PERMINTAAN</span>
                <span class="block text-sm font-bold text-slate-900 mt-1">Rp {{ number_format($realisasi['rab_permintaan'] ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Dokumen Pendukung -->
        <div>
            <span class="block text-xs font-bold text-slate-700 mb-2.5">Dokumen Pendukung</span>
            <div class="flex flex-wrap items-center gap-3">
                <!-- KAK Badge -->
                @if(!empty($realisasi['kak_file']))
                <a href="{{ $realisasi['kak_file'] }}" target="_blank" class="inline-flex items-center gap-3 px-3.5 py-2.5 bg-slate-50 hover:bg-slate-100 border border-slate-200/90 rounded-lg transition-colors group">
                    <div class="w-7 h-7 rounded bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-800 leading-tight">Dokumen KAK</span>
                        <span class="block text-[10px] text-emerald-600 font-medium">Buka File PDF</span>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-600 transition-colors ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                </a>
                @else
                <div class="inline-flex items-center gap-2 px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-400">
                    <svg class="w-4 h-4 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path></svg>
                    <span>Tidak ada KAK</span>
                </div>
                @endif

                <!-- BAST / Kuitansi Badge -->
                @php
                    $isSupply = ($realisasi['jenis'] ?? '') === 'Supply';
                    $hasDoc = !empty($realisasi['bast_file']) || !empty($realisasi['kuitansi_file']);
                    $docUrl = $realisasi['bast_file'] ?? $realisasi['kuitansi_file'];
                @endphp
                @if($hasDoc)
                <a href="{{ $docUrl }}" target="_blank" class="inline-flex items-center gap-3 px-3.5 py-2.5 bg-slate-50 hover:bg-slate-100 border border-slate-200/90 rounded-lg transition-colors group">
                    <div class="w-7 h-7 rounded bg-blue-50 text-blue-600 flex items-center justify-center">
                        @if($isSupply)
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                        @else
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                        @endif
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-800 leading-tight">{{ $isSupply ? 'Dokumen BAST' : 'Dokumen Kuitansi' }}</span>
                        <span class="block text-[10px] text-emerald-600 font-medium">Buka File {{ $isSupply ? 'BAST' : 'Kuitansi' }}</span>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-600 transition-colors ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                </a>
                @else
                <div class="inline-flex items-center gap-2 px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-400">
                    <svg class="w-4 h-4 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path></svg>
                    <span>Tidak ada {{ $isSupply ? 'BAST' : 'Kuitansi' }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    @if(($realisasi['jenis'] ?? '') === 'Supply')
        <!-- ========================================================================= -->
        <!-- KHUSUS SUPPLY: CARD DETAIL RAB PERMINTAAN SUPPLY                          -->
        <!-- ========================================================================= -->
        @php 
            $totalRabAwal = 0;
            foreach($realisasi['rabItemsPermintaan'] ?? [] as $rab) {
                $totalRabAwal += ($rab['vol'] ?? 1) * ($rab['hargaSatuan'] ?? 0);
            }
        @endphp
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="bg-[#edf2f9] px-6 py-3.5 border-b border-slate-200/80 flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-900">Detail RAB Permintaan (Supply)</h2>
                <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                    {{ count($realisasi['rabItemsPermintaan'] ?? []) }} Barang Permintaan
                </span>
            </div>
            <div class="p-6 space-y-4">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Rincian Anggaran Permintaan Barang Persediaan</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#edf2f9]/70 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                <th class="py-3 px-6 w-12 text-center">NO</th>
                                <th class="py-3 px-6">URAIAN BARANG / PERSEDIAAN</th>
                                <th class="py-3 px-6 w-24 text-center">VOL</th>
                                <th class="py-3 px-6 w-32 text-center">SATUAN</th>
                                <th class="py-3 px-6 w-44 text-right">HARGA SATUAN (RP)</th>
                                <th class="py-3 px-6 w-44 text-right pr-8">JUMLAH (RP)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                            @forelse($realisasi['rabItemsPermintaan'] ?? [] as $idx => $rab)
                                @php 
                                    $subtotal = ($rab['vol'] ?? 1) * ($rab['hargaSatuan'] ?? 0);
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-3 px-6 text-center text-xs text-slate-500 font-semibold">{{ $idx + 1 }}</td>
                                    <td class="py-3 px-6 font-medium text-slate-800">{{ $rab['uraian'] }}</td>
                                    <td class="py-3 px-6 text-center text-slate-600">{{ $rab['vol'] }}</td>
                                    <td class="py-3 px-6 text-center text-slate-600">{{ $rab['satuan'] }}</td>
                                    <td class="py-3 px-6 text-right text-slate-700">{{ number_format($rab['hargaSatuan'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="py-3 px-6 text-right font-medium text-slate-800 pr-8">{{ number_format($subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-slate-400 text-xs italic">Tidak ada rincian data RAB permintaan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-slate-200/80 bg-white">
                                <td colspan="5" class="py-4 px-6 text-right font-bold text-slate-800 text-xs uppercase tracking-wider">
                                    TOTAL RAB PERMINTAAN:
                                </td>
                                <td class="py-4 px-6 text-right font-extrabold text-[#0c4a82] text-xl pr-8">
                                    {{ number_format($totalRabAwal, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- KHUSUS SUPPLY: DETAIL RAB REALISASI (BARANG PERSEDIAAN)                   -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="bg-[#edf2f9] px-6 py-4 border-b border-slate-200/80 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                    <h2 class="text-base font-bold text-slate-900">Detail RAB Realisasi (Rincian Realisasi Barang Persediaan)</h2>
                </div>
                <span class="text-xs font-semibold text-[#0c4a82] bg-white border border-blue-200/80 px-3 py-1 rounded-md shadow-2xs">
                    Total: Rp {{ number_format($realisasi['total_realisasi'] ?? 0, 0, ',', '.') }}
                </span>
            </div>

            <div class="p-6 space-y-6">
                <!-- Tabel Realisasi Barang -->
                <div>
                    <h4 class="text-xs font-bold text-[#094886] uppercase tracking-wider mb-3">Daftar Barang Persediaan yang Direalisasikan</h4>
                    <div class="border border-slate-200/90 rounded-lg overflow-hidden shadow-2xs">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-emerald-50/60 border-b border-slate-200/80 text-[11px] font-bold text-emerald-900 uppercase tracking-wider">
                                    <th class="py-3 px-6 w-12 text-center">NO</th>
                                    <th class="py-3 px-6">NAMA BARANG / PERSEDIAAN</th>
                                    <th class="py-3 px-6 w-24 text-center">VOL</th>
                                    <th class="py-3 px-6 w-32 text-center">SATUAN</th>
                                    <th class="py-3 px-6 w-44 text-right">HARGA SATUAN (RP)</th>
                                    <th class="py-3 px-6 w-44 text-right pr-8">JUMLAH (RP)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                                @forelse($realisasi['supplyItems'] ?? [] as $idx => $item)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-3.5 px-6 text-center text-xs text-slate-500 font-semibold">{{ $idx + 1 }}</td>
                                    <td class="py-3.5 px-6 font-medium text-slate-800">{{ $item['uraian'] }}</td>
                                    <td class="py-3.5 px-6 text-center text-slate-600">{{ $item['vol'] }}</td>
                                    <td class="py-3.5 px-6 text-center text-slate-600">{{ $item['satuan'] }}</td>
                                    <td class="py-3.5 px-6 text-right text-slate-700">{{ number_format($item['hargaSatuan'], 0, ',', '.') }}</td>
                                    <td class="py-3.5 px-6 text-right font-semibold text-slate-800 pr-8">{{ number_format($item['total'] ?? ($item['vol'] * $item['hargaSatuan']), 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="py-4 text-center text-slate-400 text-xs italic">Tidak ada rincian barang persediaan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-slate-200/80 bg-white">
                                    <td colspan="5" class="py-4 px-6 text-right font-bold text-slate-800 text-xs">
                                        Total Realisasi Persediaan:
                                    </td>
                                    <td class="py-4 px-6 text-right font-extrabold text-emerald-800 text-xl pr-8">
                                        Rp {{ number_format($realisasi['total_realisasi'] ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Dokumentasi Foto Persediaan -->
                @if(!empty($realisasi['supplyPhotos']))
                <div class="border-t border-slate-100 pt-5">
                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3">Dokumentasi Foto Pengadaan Persediaan</h4>
                    <div class="flex flex-wrap items-center gap-4">
                        @foreach($realisasi['supplyPhotos'] as $photoIndex => $photo)
                        <div class="relative group overflow-hidden rounded-lg border border-slate-200 shadow-2xs">
                            <a href="{{ $photo }}" target="_blank">
                                <img class="w-36 h-28 object-cover group-hover:scale-105 transition-transform duration-200 cursor-pointer" 
                                     src="{{ $photo }}" 
                                     alt="Dokumentasi Foto Persediaan {{ $photoIndex + 1 }}">
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

    @else
        <!-- ========================================================================= -->
        <!-- KHUSUS MAINTENANCE: TETAP PER ASET (JANGAN DIUBAH APAPUN)                  -->
        <!-- ========================================================================= -->

        <!-- Card: RAB Permintaan -->
        @if(!empty($realisasi['rabItemsPermintaan']))
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="bg-[#edf2f9] px-6 py-3.5 border-b border-slate-200/80">
                <h2 class="text-base font-bold text-slate-900">RAB Permintaan</h2>
            </div>
            <div class="p-6 space-y-4">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Rincian Anggaran Realisasi Aset</h3>
                <div class="overflow-x-auto" x-data="{ expandedRAB: null }">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#edf2f9]/70 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                <th class="py-3 px-6">URAIAN ASET / PEKERJAAN</th>
                                <th class="py-3 px-6 w-24 text-center">VOL</th>
                                <th class="py-3 px-6 w-32 text-center">SATUAN</th>
                                <th class="py-3 px-6 w-44 text-right">HARGA SATUAN (RP)</th>
                                <th class="py-3 px-6 w-44 text-right pr-8">JUMLAH (RP)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                            @php $totalRabAwal = 0; @endphp
                            @foreach($realisasi['asetList'] ?? [] as $index => $aset)
                                @php 
                                    $subtotalRab = collect($aset['rabItems'] ?? [])->sum(function($item) { 
                                        return $item['vol'] * $item['hargaSatuan']; 
                                    }); 
                                    $totalRabAwal += $subtotalRab;
                                @endphp
                                
                                <!-- Aset Header -->
                                <tr class="hover:bg-blue-50/30 transition-colors cursor-pointer bg-slate-50/30" @click="expandedRAB === {{ $index }} ? expandedRAB = null : expandedRAB = {{ $index }}">
                                    <td class="py-3 px-6 font-bold text-slate-800">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="expandedRAB === {{ $index }} ? 'rotate-90' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="9 18 15 12 9 6"></polyline>
                                            </svg>
                                            {{ $aset['nama'] }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-6 text-center"></td>
                                    <td class="py-3 px-6 text-center"></td>
                                    <td class="py-3 px-6 text-right"></td>
                                    <td class="py-3 px-6 text-right font-bold text-[#0c4a82] pr-8">{{ number_format($subtotalRab, 0, ',', '.') }}</td>
                                </tr>

                                <!-- Uraian Items -->
                                @forelse($aset['rabItems'] ?? [] as $rab)
                                <tr x-show="expandedRAB === {{ $index }}" style="display: none;" class="hover:bg-slate-50/40">
                                    <td class="py-3.5 px-6 pl-12 text-sm font-medium text-slate-700">{{ $rab['uraian'] }}</td>
                                    <td class="py-3.5 px-6 text-center text-slate-600">{{ $rab['vol'] }}</td>
                                    <td class="py-3.5 px-6 text-center text-slate-600">{{ $rab['satuan'] }}</td>
                                    <td class="py-3.5 px-6 text-right text-slate-700">{{ number_format($rab['hargaSatuan'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="py-3.5 px-6 text-right font-medium text-slate-800 pr-8">{{ number_format(($rab['vol'] * $rab['hargaSatuan']), 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr x-show="expandedRAB === {{ $index }}" style="display: none;">
                                    <td colspan="5" class="py-3 px-6 pl-12 text-sm text-slate-400 italic">Tidak ada rincian RAB untuk aset ini.</td>
                                </tr>
                                @endforelse
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-slate-200/80 bg-white">
                                <td colspan="4" class="py-4 px-6 text-right font-bold text-slate-800 text-xs uppercase tracking-wider">
                                    TOTAL RAB KESELURUHAN:
                                </td>
                                <td class="py-4 px-6 text-right font-extrabold text-[#0c4a82] text-xl pr-8">
                                    {{ number_format($totalRabAwal, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Section: Biaya Realisasi (Dipisah per Aset dengan Fitur Buka/Tutup Accordion) -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900 tracking-tight">Biaya Realisasi per Aset</h2>
                <span class="text-xs text-slate-500 font-medium">Klik pada judul aset untuk membuka / menutup rincian</span>
            </div>

            @forelse($realisasi['asetList'] ?? [] as $asetIndex => $aset)
            <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden transition-all" x-data="{ open: false }">
                <!-- Collapsible Accordion Header -->
                <button type="button" @click="open = !open" 
                        class="w-full bg-[#edf2f9] hover:bg-[#e4ebf6] px-6 py-4 border-b border-slate-200/80 flex items-center justify-between transition-colors text-left cursor-pointer">
                    <div class="flex items-center gap-3">
                        <!-- Expand/Collapse Chevron Indicator -->
                        <div class="w-6 h-6 rounded-full bg-white text-slate-600 flex items-center justify-center shadow-2xs transition-transform duration-200"
                             :class="{ 'rotate-180': open }">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                        <div>
                            <span class="text-base font-bold text-slate-900">Aset {{ $asetIndex + 1 }}: {{ $aset['nama'] }}</span>
                            @if(!empty($aset['kode_inventaris']) && $aset['kode_inventaris'] !== '-')
                                <span class="block text-xs text-slate-500 mt-0.5">Kode: {{ $aset['kode_inventaris'] }}</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-semibold text-slate-500">Subtotal Realisasi:</span>
                        <span class="text-sm font-extrabold text-[#0c4a82] bg-white border border-blue-200/80 px-3 py-1 rounded-md shadow-2xs">
                            Rp {{ number_format($aset['subtotal'] ?? 0, 0, ',', '.') }}
                        </span>
                    </div>
                </button>

                <!-- Collapsible Body -->
                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-6 space-y-6">
                    <!-- Detail Biaya Realisasi Table -->
                    <div>
                        <h4 class="text-xs font-bold text-[#094886] uppercase tracking-wider mb-3">Detail Biaya Realisasi</h4>
                        <div class="border border-slate-200/90 rounded-lg overflow-hidden shadow-2xs">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-[#edf2f9]/70 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                        <th class="py-3 px-6">URAIAN PEKERJAAN / SPAREPART</th>
                                        <th class="py-3 px-6 w-24 text-center">VOL</th>
                                        <th class="py-3 px-6 w-32 text-center">SATUAN</th>
                                        <th class="py-3 px-6 w-44 text-right">HARGA SATUAN (RP)</th>
                                        <th class="py-3 px-6 w-44 text-right pr-8">JUMLAH (RP)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                                    @forelse($aset['items'] ?? [] as $item)
                                    <tr class="hover:bg-blue-50/30 transition-colors">
                                        <td class="py-3.5 px-6 font-medium text-slate-800">{{ $item['uraian'] }}</td>
                                        <td class="py-3.5 px-6 text-center text-slate-600">{{ $item['vol'] }}</td>
                                        <td class="py-3.5 px-6 text-center text-slate-600">{{ $item['satuan'] }}</td>
                                        <td class="py-3.5 px-6 text-right text-slate-700">{{ number_format($item['hargaSatuan'], 0, ',', '.') }}</td>
                                        <td class="py-3.5 px-6 text-right font-medium text-slate-800 pr-8">{{ number_format($item['total'], 0, ',', '.') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="py-4 text-center text-slate-400 text-xs">Tidak ada rincian item realisasi.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="border-t border-slate-200/80 bg-white">
                                        <td colspan="4" class="py-4 px-6 text-right font-bold text-slate-800 text-xs">
                                            Total Realisasi Aset {{ $asetIndex + 1 }} ({{ $aset['nama'] }}):
                                        </td>
                                        <td class="py-4 px-6 text-right font-extrabold text-[#0c4a82] text-xl pr-8">
                                            {{ number_format($aset['subtotal'] ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Dokumentasi Foto per Aset -->
                    @if(!empty($aset['photos']))
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3">Dokumentasi Foto</h4>
                        <div class="flex flex-wrap items-center gap-4">
                            @foreach($aset['photos'] as $photoIndex => $photo)
                            <div class="relative group overflow-hidden rounded-lg border border-slate-200 shadow-2xs">
                                <a href="{{ $photo }}" target="_blank">
                                    <img class="w-36 h-28 object-cover group-hover:scale-105 transition-transform duration-200 cursor-pointer" 
                                         src="{{ $photo }}" 
                                         alt="Dokumentasi Foto {{ $photoIndex + 1 }}">
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl border border-slate-200/90 p-8 text-center text-slate-400">
                Belum ada rincian aset realisasi.
            </div>
            @endforelse
        </div>
    @endif

    <!-- Summary Total Card -->
    <div class="bg-[#dce9fa] border border-blue-200/80 rounded-xl p-5 shadow-xs flex items-center justify-between">
        <span class="text-base sm:text-lg font-bold text-slate-900">Total Keseluruhan Biaya Realisasi</span>
        <span class="text-xl sm:text-2xl font-extrabold text-[#0c3968]">
            Rp {{ number_format($realisasi['total_realisasi'] ?? 0, 0, ',', '.') }}
        </span>
    </div>

    <!-- Bottom Action Buttons -->
    <div class="flex flex-wrap items-center justify-end gap-3 pt-4 border-t border-slate-200/80">
        <!-- Cetak Laporan -->
        <button type="button" onclick="window.print()" 
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-[#094886] text-[#094886] hover:bg-blue-50/80 text-sm font-bold rounded-lg shadow-xs transition-colors cursor-pointer">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            <span>Cetak Laporan</span>
        </button>

        @if(!\App\Services\SatkerService::isViewingOtherSatker())
        <!-- Edit Realisasi -->
        <a href="{{ route('realisasi.edit', $realisasi['id'] ?? 1) }}" 
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-[#094886] text-[#094886] hover:bg-blue-50/80 text-sm font-bold rounded-lg shadow-xs transition-colors">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
            </svg>
            <span>Edit Realisasi</span>
        </a>
        @endif

        <!-- Kembali -->
        <a href="{{ route('realisasi.index') }}" 
           class="inline-flex items-center px-6 py-2.5 bg-[#094886] hover:bg-[#073868] text-white text-sm font-bold rounded-lg shadow-xs transition-colors">
            Kembali
        </a>
    </div>
</div>
@endsection
