@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 pb-16">
    <!-- Header Title & Action Buttons -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl sm:text-[26px] font-bold text-slate-900 tracking-tight">Detail Permintaan</h1>
            <p class="text-sm text-slate-500 mt-1">Informasi lengkap rincian permintaan dan rencana anggaran biaya (RAB).</p>
        </div>
        <a href="{{ route('permintaan.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 hover:bg-slate-50 px-3.5 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            <span>Kembali ke Daftar</span>
        </a>
    </div>

    <!-- Card: Informasi Permintaan (Pekerjaan & Tanggal) -->
    <div class="bg-white rounded-xl border border-slate-200/90 p-6 shadow-xs">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 250px;">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">PEKERJAAN / KEPERLUAN</span>
                    @if(($permintaan['jenis'] ?? '') === 'Supply')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Supply (Persediaan)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                            Maintenance (Pemeliharaan)
                        </span>
                    @endif
                </div>
                <p class="text-base sm:text-lg font-bold text-slate-900 leading-relaxed">
                    {{ $permintaan['pekerjaan'] ?? '-' }}
                </p>
            </div>
            <div class="bg-slate-50 border border-slate-200/80 rounded-xl px-4 py-3" style="min-width: 170px; text-align: right;">
                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">TANGGAL PERMINTAAN</span>
                <span class="text-sm font-bold text-slate-800 mt-0.5 block font-mono">
                    {{ $permintaan['tanggal'] ?? '-' }}
                </span>
            </div>
        </div>
    </div>

    <!-- List of Asset / Supply Cards -->
    @php
        $isSupply = ($permintaan['jenis'] ?? '') === 'Supply';
    @endphp

    @foreach($permintaan['asetList'] ?? [] as $index => $aset)
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
        <!-- Header -->
        <div class="bg-[#edf2f9] px-6 py-3.5 border-b border-slate-200/80 flex items-center justify-between">
            <h2 class="text-base sm:text-lg font-bold text-slate-900">
                {{ $isSupply ? 'Item Persediaan ' . ($index + 1) : 'Aset ' . ($index + 1) }}
            </h2>
            <span class="text-xs font-medium text-slate-500">
                {{ $isSupply ? 'Daftar Kebutuhan Barang' : 'Data Aset BMN' }}
            </span>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-5">
            <div>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider pb-2 border-b border-slate-100">
                    {{ $isSupply ? 'Informasi Barang Persediaan' : 'Data Aset' }}
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                    <div>
                        <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            {{ $isSupply ? 'NAMA BARANG PERSEDIAAN' : 'NAMA ASET' }}
                        </span>
                        <span class="block text-base font-bold text-slate-900 mt-0.5">{{ $aset['nama_aset'] }}</span>
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            {{ $isSupply ? 'JENIS / KATEGORI' : 'KODE INVENTARIS' }}
                        </span>
                        <span class="block text-base font-bold text-slate-900 mt-0.5">{{ $aset['kode_inventaris'] }}</span>
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            {{ $isSupply ? 'STATUS / KONDISI' : 'KONDISI ASET' }}
                        </span>
                        <div class="mt-1 flex items-center gap-2">
                            @if($isSupply)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    Barang Baru / Persediaan
                                </span>
                            @elseif(strtolower(trim($aset['kondisi_aset'] ?? '')) === 'rusak berat')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                    Rusak Berat
                                </span>
                            @elseif(strtolower(trim($aset['kondisi_aset'] ?? '')) === 'rusak ringan')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                    Rusak Ringan
                                </span>
                            @elseif(strtolower(trim($aset['kondisi_aset'] ?? '')) === 'baik')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    Baik
                                </span>
                            @else
                                <span class="text-sm font-semibold text-slate-700">{{ $aset['kondisi_aset'] ?? '-' }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if(!$isSupply)
                <!-- Deskripsi Perbaikan / Kerusakan -->
                <div class="grid grid-cols-1 gap-4 mt-4">
                    <div class="col-span-1">
                        <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                            DESKRIPSI PERBAIKAN / KERUSAKAN
                        </span>
                        <div class="bg-slate-50 border border-slate-200/80 rounded-lg p-3.5 text-sm text-slate-700">
                            {{ $aset['deskripsi_kerusakan'] ?? '-' }}
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Rencana Anggaran Biaya (RAB) -->
            <div class="border border-slate-200/90 rounded-lg overflow-hidden mt-6">
                <div class="bg-[#edf2f9] px-4 py-2.5 border-b border-slate-200/80">
                    <h4 class="text-xs font-bold text-slate-800">Rencana Anggaran Biaya (RAB)</h4>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#edf2f9]/70 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                <th class="py-3 px-4 w-12 text-center">NO</th>
                                <th class="py-3 px-4">DESKRIPSI ITEM</th>
                                <th class="py-3 px-4 w-24 text-center">VOLUME</th>
                                <th class="py-3 px-4 w-28 text-center">SATUAN</th>
                                <th class="py-3 px-4 w-40 text-right">HARGA SATUAN (RP)</th>
                                <th class="py-3 px-4 w-40 text-right pr-6">TOTAL (RP)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                            @php $subtotal = 0; @endphp
                            @foreach($aset['rabItems'] ?? [] as $rabIndex => $item)
                            @php 
                                $itemTotal = ($item['volume'] ?? 1) * ($item['hargaSatuan'] ?? 0);
                                $subtotal += $itemTotal;
                            @endphp
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <td class="py-3 px-4 text-center text-slate-600">{{ $rabIndex + 1 }}</td>
                                <td class="py-3 px-4 font-medium text-slate-800">{{ $item['uraian'] }}</td>
                                <td class="py-3 px-4 text-center text-slate-600">{{ $item['volume'] }}</td>
                                <td class="py-3 px-4 text-center text-slate-600">{{ $item['satuan'] }}</td>
                                <td class="py-3 px-4 text-right text-slate-700">{{ number_format($item['hargaSatuan'], 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right font-medium text-slate-900 pr-6">{{ number_format($itemTotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-slate-200/80 bg-white">
                                <td colspan="5" class="py-4 px-6 text-right font-bold text-slate-800 text-sm">
                                    Subtotal Anggaran {{ $isSupply ? 'Item ' . ($index + 1) : 'Aset ' . ($index + 1) }}
                                </td>
                                <td class="py-4 px-6 text-right font-extrabold text-[#0c4a82] text-xl pr-6">
                                    {{ number_format($subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <!-- Total Seluruh Anggaran Summary Card -->
    <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs flex flex-col sm:flex-row items-center justify-end gap-4">
        <span class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">TOTAL SELURUH ANGGARAN</span>
        <div class="bg-[#edf4fb] border border-blue-200/80 rounded-lg px-8 py-2.5 min-w-[220px] text-right">
            <span class="text-2xl font-extrabold text-[#0c4a82]">
                {{ number_format($permintaan['total_seluruh_anggaran'] ?? 0, 0, ',', '.') }}
            </span>
        </div>
    </div>

    <!-- Bottom Action Buttons -->
    <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
        <!-- Tombol Kembali -->
        <a href="{{ route('permintaan.index') }}" 
           class="inline-flex items-center justify-center px-6 py-2.5 bg-white border border-[#094886] text-[#094886] hover:bg-blue-50/80 text-sm font-bold rounded-lg shadow-xs transition-colors">
            Kembali
        </a>

        <!-- Tombol Edit Permintaan -->
        <a href="{{ route('permintaan.edit', $permintaan['id'] ?? 1) }}" 
           class="inline-flex items-center justify-center px-6 py-2.5 bg-[#094886] hover:bg-[#073868] text-white text-sm font-bold rounded-lg shadow-xs transition-all">
            Edit Permintaan
        </a>

        <!-- Tombol Generate KAK -->
        <a href="{{ route('permintaan.kak', $permintaan['id']) }}" 
           class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-[#063b6f] hover:bg-[#04284d] text-white text-sm font-bold rounded-lg shadow-xs transition-all cursor-pointer">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
            </svg>
            <span>Generate KAK</span>
        </a>

        {{-- Tombol Generate BAST (Khusus Supply) --}}
        @if(($permintaan['jenis'] ?? '') === 'Supply')
        <a href="{{ route('permintaan.bast', $permintaan['id']) }}" 
           class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-bold rounded-lg shadow-xs transition-all cursor-pointer">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 11l3 3L22 4"></path>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
            </svg>
            <span>Generate BAST</span>
        </a>
        @endif
    </div>
</div>
@endsection
