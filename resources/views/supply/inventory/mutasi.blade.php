@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex items-center gap-3">
        <a href="{{ route('supply.inventory.index') }}" 
           class="p-2 text-slate-400 hover:text-slate-600 hover:bg-white rounded-lg border border-slate-200 shadow-2xs transition-all">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Riwayat Mutasi Stok</h1>
            <p class="text-sm text-slate-500 mt-0.5">Catatan keluar masuk barang persediaan.</p>
        </div>
    </div>

    <!-- Info Barang Card -->
    <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">KODE BARANG</span>
                <div class="text-sm font-mono font-bold text-[#094886] mt-1">{{ $supply->kode_barang }}</div>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">NAMA BARANG</span>
                <div class="text-sm font-bold text-slate-900 mt-1">{{ $supply->nama_barang }}</div>
                @if($supply->spesifikasi)
                    <div class="text-xs text-slate-500 mt-0.5">{{ $supply->spesifikasi }}</div>
                @endif
            </div>
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">KATEGORI</span>
                <div class="text-sm font-semibold text-slate-700 mt-1">{{ $supply->kategori }}</div>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">SISA STOK SAAT INI</span>
                <div class="text-2xl font-extrabold {{ $supply->stok <= 0 ? 'text-rose-600' : ($supply->stok <= $supply->stok_minimum ? 'text-amber-600' : 'text-emerald-600') }} mt-1">
                    {{ $supply->stok }} <span class="text-xs font-semibold text-slate-500">{{ $supply->satuan }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Mutasi List Table -->
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-900 text-base">Kartu Mutasi Persediaan</h2>
            <span class="text-xs text-slate-500">Total {{ $supply->mutasis->count() }} transaksi</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold text-slate-600 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 w-12 text-center">NO</th>
                        <th class="px-5 py-3">TANGGAL</th>
                        <th class="px-5 py-3 text-center">JENIS</th>
                        <th class="px-5 py-3 text-right">JUMLAH</th>
                        <th class="px-5 py-3 text-center">PERUBAHAN STOK</th>
                        <th class="px-5 py-3">KETERANGAN / DOKUMEN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($supply->mutasis as $idx => $m)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-5 py-3.5 text-center text-xs text-slate-400 font-medium">{{ $idx + 1 }}</td>
                        <td class="px-5 py-3.5 font-medium text-slate-900 text-xs sm:text-sm">
                            {{ $m->tanggal ? $m->tanggal->translatedFormat('d M Y') : '-' }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($m->jenis === 'masuk')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    Masuk
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                    Keluar (Distribusi)
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right font-bold {{ $m->jenis === 'masuk' ? 'text-emerald-600' : 'text-blue-700' }}">
                            {{ $m->jenis === 'masuk' ? '+' : '-' }}{{ $m->jumlah }} {{ $supply->satuan }}
                        </td>
                        <td class="px-5 py-3.5 text-center text-xs text-slate-500 font-mono">
                            {{ $m->stok_sebelum }} → <span class="font-bold text-slate-800">{{ $m->stok_sesudah }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-600">
                            <div>{{ $m->keterangan ?? '-' }}</div>
                            @if($m->distribusi)
                                <a href="{{ route('supply.distribusi.show', $m->distribusi->id) }}" class="inline-flex items-center gap-1 text-[#094886] hover:underline font-semibold mt-0.5">
                                    <span>Lihat {{ $m->distribusi->nomor_bast }}</span>
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                </a>
                            @endif
                            @if($m->realisasi)
                                <a href="{{ route('realisasi.show', $m->realisasi->id) }}" class="inline-flex items-center gap-1 text-[#094886] hover:underline font-semibold mt-0.5">
                                    <span>Lihat Realisasi {{ $m->realisasi->no_fpa ? ('FPA: '.$m->realisasi->no_fpa) : '#'.$m->realisasi->id }}</span>
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-400">
                            Belum ada riwayat mutasi stok untuk barang ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
