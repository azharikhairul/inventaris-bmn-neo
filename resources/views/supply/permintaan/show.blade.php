@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-16">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('supply.permintaan.index') }}" 
               class="p-2 text-slate-400 hover:text-slate-600 hover:bg-white rounded-lg border border-slate-200 shadow-2xs transition-all">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Detail Permintaan Barang</h1>
                    @if($permintaan->status === 'Didistribusikan')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Didistribusikan
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            Menunggu Distribusi
                        </span>
                    @endif
                </div>
                <p class="text-sm text-slate-500 font-mono mt-0.5">{{ $permintaan->nomor_permintaan }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if($permintaan->distribusi)
                <a href="{{ route('supply.distribusi.show', $permintaan->distribusi->id) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-lg shadow-sm transition-all">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                    <span>Lihat Dokumen BAST</span>
                </a>
            @endif

            @if($permintaan->status === 'Menunggu' && !\App\Services\SatkerService::isViewingOtherSatker())
                <form action="{{ route('supply.permintaan.destroy', $permintaan->id) }}" method="POST" 
                      onsubmit="return confirm('Apakah Anda yakin ingin membatalkan/menghapus permintaan ini?')" class="inline m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-3.5 py-2 text-rose-600 hover:bg-rose-50 text-xs sm:text-sm font-semibold rounded-lg border border-rose-200 transition-colors cursor-pointer">
                        Batalkan Permintaan
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Info Pemohon & Keperluan -->
    <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-xs">
        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">DATA PERMOHONAN</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <span class="text-xs text-slate-500">Tanggal Pengajuan:</span>
                <div class="text-sm font-bold text-slate-900 mt-0.5">
                    {{ $permintaan->tanggal_permintaan ? $permintaan->tanggal_permintaan->translatedFormat('d F Y') : '-' }}
                </div>
            </div>
            <div>
                <span class="text-xs text-slate-500">Unit Kerja / Seksi:</span>
                <div class="text-sm font-bold text-slate-900 mt-0.5">{{ $permintaan->unit_kerja }}</div>
            </div>
            <div>
                <span class="text-xs text-slate-500">Nama Pemohon:</span>
                <div class="text-sm font-bold text-slate-900 mt-0.5">{{ $permintaan->nama_pemohon }}</div>
                @if($permintaan->nip_pemohon)
                    <div class="text-xs text-slate-500">NIP: {{ $permintaan->nip_pemohon }}</div>
                @endif
            </div>
            <div>
                <span class="text-xs text-slate-500">Status:</span>
                <div class="text-sm font-bold text-slate-900 mt-0.5">{{ $permintaan->status }}</div>
            </div>
        </div>

        <div class="mt-4 pt-4 border-t border-slate-100">
            <span class="text-xs text-slate-500">Keperluan / Penjelasan Kegiatan:</span>
            <p class="text-sm text-slate-800 mt-1 leading-relaxed bg-slate-50 p-3 rounded-lg border border-slate-200/60">
                {{ $permintaan->keperluan }}
            </p>
        </div>
    </div>

    <!-- Banner Jika Sudah Didistribusikan -->
    @if($permintaan->status === 'Didistribusikan' && $permintaan->distribusi)
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-emerald-900">Barang Telah Didistribusikan & BAST Terbit</h3>
                <p class="text-xs text-emerald-700 mt-0.5">
                    Nomor BAST: <strong>{{ $permintaan->distribusi->nomor_bast }}</strong> pada tanggal {{ $permintaan->distribusi->tanggal_bast ? $permintaan->distribusi->tanggal_bast->translatedFormat('d F Y') : '-' }}.
                </p>
                <div class="mt-2 text-xs font-semibold text-emerald-800">
                    Status BAST: {{ $permintaan->distribusi->status }} 
                    @if($permintaan->distribusi->status === 'Selesai')
                        (Hasil scan tanda tangan manual sudah diunggah)
                    @else
                        (Menunggu cetak & tanda tangan manual)
                    @endif
                </div>
            </div>
        </div>
        <a href="{{ route('supply.distribusi.show', $permintaan->distribusi->id) }}" 
           class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-lg shadow-2xs whitespace-nowrap">
            <span>Buka Dokumen BAST</span>
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </a>
    </div>
    @endif

    <!-- Daftar Barang yang Diminta -->
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-900 text-base">Rincian Barang Persediaan</h2>
            <span class="text-xs text-slate-500">{{ $permintaan->items->count() }} item barang</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold text-slate-600 uppercase">
                    <tr>
                        <th class="px-5 py-3 w-12 text-center">NO</th>
                        <th class="px-5 py-3">KODE & NAMA BARANG</th>
                        <th class="px-5 py-3 text-center">STOK GUDANG</th>
                        <th class="px-5 py-3 text-center">JUMLAH DIMINTA</th>
                        @if($permintaan->status === 'Didistribusikan')
                            <th class="px-5 py-3 text-center">JUMLAH DISERAHKAN</th>
                        @endif
                        <th class="px-5 py-3">CATATAN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($permintaan->items as $idx => $it)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-5 py-3.5 text-center text-xs text-slate-400 font-medium">{{ $idx + 1 }}</td>
                        <td class="px-5 py-3.5">
                            <div class="font-mono text-xs font-bold text-[#094886]">{{ $it->supply->kode_barang ?? '-' }}</div>
                            <div class="font-bold text-slate-900 mt-0.5">{{ $it->supply->nama_barang ?? 'Barang Persediaan' }}</div>
                            @if($it->supply && $it->supply->spesifikasi)
                                <div class="text-xs text-slate-400 mt-0.5">{{ $it->supply->spesifikasi }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($it->supply)
                                <span class="text-xs font-bold {{ $it->supply->stok < $it->jumlah_diminta ? 'text-rose-600' : 'text-slate-700' }}">
                                    {{ $it->supply->stok }} {{ $it->satuan }}
                                </span>
                            @else
                                <span class="text-xs text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-center font-bold text-slate-900">
                            {{ $it->jumlah_diminta }} {{ $it->satuan }}
                        </td>
                        @if($permintaan->status === 'Didistribusikan')
                            <td class="px-5 py-3.5 text-center font-bold text-emerald-600">
                                {{ $it->jumlah_disetujui ?? $it->jumlah_diminta }} {{ $it->satuan }}
                            </td>
                        @endif
                        <td class="px-5 py-3.5 text-xs text-slate-500">
                            {{ $it->catatan ?: '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- FORM PROSES DISTRIBUSI & GENERATE BAST (Hanya tampil jika status Menunggu) -->
    @if($permintaan->status === 'Menunggu')
    @if(!\App\Services\SatkerService::isViewingOtherSatker())
    <div class="bg-white rounded-xl border-2 border-blue-200/80 p-5 sm:p-6 shadow-sm space-y-5">
        <div class="flex items-start gap-3 border-b border-slate-100 pb-4">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#094886] flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900">Proses Distribusi & Terbitkan BAST Otomatis</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Konfirmasi jumlah barang yang diserahkan. Sistem akan <strong>mengurangi stok persediaan</strong> secara otomatis dan menerbitkan dokumen <strong>Berita Acara Serah Terima (BAST)</strong> resmi.
                </p>
            </div>
        </div>

        <form action="{{ route('supply.permintaan.distribusikan', $permintaan->id) }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <!-- Tanggal BAST -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tanggal BAST / Distribusi <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal_bast" value="{{ date('Y-m-d') }}" required 
                           class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>
            </div>

            <!-- Konfirmasi Jumlah Diserahkan Per Item -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Konfirmasi Jumlah Barang yang Diserahkan & Dipotong dari Stok:
                </label>
                <div class="space-y-2">
                    @foreach($permintaan->items as $idx => $it)
                    <div class="flex items-center justify-between p-3 bg-slate-50 border border-slate-200/80 rounded-lg gap-4">
                        <input type="hidden" name="items[{{ $idx }}][id]" value="{{ $it->id }}">
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-slate-900 text-xs sm:text-sm truncate">
                                {{ $it->supply->nama_barang ?? 'Barang Persediaan' }}
                            </div>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                Permintaan: {{ $it->jumlah_diminta }} {{ $it->satuan }} • Sisa stok: {{ $it->supply->stok ?? 0 }} {{ $it->satuan }}
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <label class="text-xs text-slate-600 font-semibold whitespace-nowrap">Diserahkan:</label>
                            <input type="number" 
                                   name="items[{{ $idx }}][jumlah_disetujui]" 
                                   value="{{ min($it->jumlah_diminta, $it->supply->stok ?? $it->jumlah_diminta) }}" 
                                   min="0" 
                                   max="{{ $it->supply->stok ?? 99999 }}" 
                                   required 
                                   class="w-20 bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-sm text-center font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                            <span class="text-xs text-slate-500 font-medium">{{ $it->satuan }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end">
                <button type="submit" 
                        onclick="return confirm('Apakah Anda yakin data distribusi sudah benar? Stok persediaan akan langsung dipotong dan BAST akan diterbitkan.')"
                        class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Proses Distribusi & Terbitkan BAST</span>
                </button>
            </div>
        </form>
    </div>
    @else
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        <div>
            <h4 class="text-sm font-bold text-amber-900">Mode Lihat Saja (Read-Only Satker)</h4>
            <p class="text-xs text-amber-700 mt-0.5">Pemrosesan distribusi supply dan penerbitan BAST hanya dapat dilakukan oleh Admin satker terkait atau saat berada di Satker Induk.</p>
        </div>
    </div>
    @endif
    @endif

</div>
@endsection
