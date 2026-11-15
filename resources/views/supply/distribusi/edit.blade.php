@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-20">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('supply.distribusi.show', $distribusi->id) }}" 
               class="p-2 text-slate-400 hover:text-slate-600 hover:bg-white rounded-lg border border-slate-200 shadow-2xs transition-all"
               title="Kembali ke Dokumen BAST">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Penyesuaian Data Dokumen BAST</h1>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        Edit BAST
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 font-mono text-[#094886] font-bold">
                    {{ $distribusi->nomor_bast }}
                </p>
            </div>
        </div>
        <div>
            <a href="{{ route('supply.distribusi.show', $distribusi->id) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold rounded-lg shadow-2xs transition-all">
                <span>Batal & Kembali</span>
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl shadow-xs">
        <div class="font-bold text-sm mb-1">Terdapat kesalahan input:</div>
        <ul class="list-disc list-inside text-xs space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('supply.distribusi.update', $distribusi->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Card 1: Informasi Dasar BAST -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-xs space-y-4">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-[#094886]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                <span>Informasi Penomoran & Tanggal BAST</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Nomor BAST <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nomor_bast" value="{{ old('nomor_bast', $distribusi->nomor_bast) }}" required
                           class="w-full text-sm font-mono font-bold bg-white border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    <span class="text-[11px] text-slate-400 mt-1 block">Sesuaikan jika memiliki nomor agenda resmi khusus.</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Tanggal Serah Terima / BAST <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal_bast" value="{{ old('tanggal_bast', $distribusi->tanggal_bast ? $distribusi->tanggal_bast->format('Y-m-d') : '') }}" required
                           class="w-full text-sm bg-white border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    Keperluan / Kegiatan
                </label>
                <input type="text" name="kegiatan_keperluan" value="{{ old('kegiatan_keperluan', $distribusi->kegiatan_keperluan) }}" 
                       placeholder="Contoh: Operasional bulanan, pencetakan sensus..."
                       class="w-full text-sm bg-white border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
            </div>
        </div>

        <!-- Card 2: Pihak Pertama & Kedua -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Pihak Pertama (Penyerah) -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs space-y-3.5">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                        <span>Pihak Pertama (Yang Menyerahkan)</span>
                    </h4>
                    <span class="text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full">Gudang / BMN</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="pihak_pertama_nama" value="{{ old('pihak_pertama_nama', $distribusi->pihak_pertama_nama) }}" required
                           class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">NIP (Opsional)</label>
                    <input type="text" name="pihak_pertama_nip" value="{{ old('pihak_pertama_nip', $distribusi->pihak_pertama_nip) }}" placeholder="1990xxxx..."
                           class="w-full text-xs bg-white border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jabatan <span class="text-rose-500">*</span></label>
                    <input type="text" name="pihak_pertama_jabatan" value="{{ old('pihak_pertama_jabatan', $distribusi->pihak_pertama_jabatan) }}" required
                           class="w-full text-xs bg-white border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>
            </div>

            <!-- Pihak Kedua (Penerima) -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs space-y-3.5">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        <span>Pihak Kedua (Yang Menerima)</span>
                    </h4>
                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">Pemohon</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Penerima <span class="text-rose-500">*</span></label>
                    <input type="text" name="pihak_kedua_nama" value="{{ old('pihak_kedua_nama', $distribusi->pihak_kedua_nama) }}" required
                           class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">NIP (Opsional)</label>
                    <input type="text" name="pihak_kedua_nip" value="{{ old('pihak_kedua_nip', $distribusi->pihak_kedua_nip) }}" placeholder="1995xxxx..."
                           class="w-full text-xs bg-white border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jabatan</label>
                        <input type="text" name="pihak_kedua_jabatan" value="{{ old('pihak_kedua_jabatan', $distribusi->pihak_kedua_jabatan) }}" placeholder="Staff / Pranata..."
                               class="w-full text-xs bg-white border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Unit Kerja / Bagian <span class="text-rose-500">*</span></label>
                        <input type="text" name="pihak_kedua_unit_kerja" value="{{ old('pihak_kedua_unit_kerja', $distribusi->pihak_kedua_unit_kerja) }}" required
                               class="w-full text-xs bg-white border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Rincian Barang yang Diserahkan -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="p-5 pb-3 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Rincian Barang & Keterangan BAST</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Sesuaikan kuantitas penyerahan, spesifikasi, dan catatan keterangan per barang.</p>
                </div>
                <span class="px-2.5 py-1 bg-blue-50 text-[#094886] font-bold text-xs rounded-full border border-blue-200">
                    {{ $distribusi->items->count() }} Jenis Barang
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm text-slate-700">
                    <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase">
                        <tr>
                            <th class="px-4 py-3 w-10 text-center">NO</th>
                            <th class="px-4 py-3 min-w-[200px]">NAMA BARANG</th>
                            <th class="px-4 py-3 min-w-[150px]">SPESIFIKASI</th>
                            <th class="px-4 py-3 w-28 text-center">JUMLAH</th>
                            <th class="px-4 py-3 w-24 text-center">SATUAN</th>
                            <th class="px-4 py-3 min-w-[200px]">KETERANGAN</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($distribusi->items as $idx => $it)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3 text-center text-slate-400 font-medium">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3">
                                <span class="font-bold text-slate-900">{{ $it->nama_barang }}</span>
                                @if($it->supply)
                                    <span class="block text-[11px] text-slate-500 font-mono">Kode: {{ $it->supply->kode_barang }} (Sisa Stok: {{ $it->supply->stok }} {{ $it->supply->satuan }})</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <input type="text" name="items[{{ $it->id }}][spesifikasi]" value="{{ old("items.{$it->id}.spesifikasi", $it->spesifikasi) }}"
                                       class="w-full text-xs bg-white border border-slate-300 rounded p-1.5">
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input type="number" min="1" name="items[{{ $it->id }}][jumlah]" value="{{ old("items.{$it->id}.jumlah", $it->jumlah) }}" required
                                       class="w-20 text-center font-bold text-xs bg-white border border-slate-300 rounded p-1.5 focus:ring-1 focus:ring-blue-500">
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input type="text" name="items[{{ $it->id }}][satuan]" value="{{ old("items.{$it->id}.satuan", $it->satuan) }}"
                                       class="w-20 text-center text-xs bg-white border border-slate-300 rounded p-1.5">
                            </td>
                            <td class="px-4 py-3">
                                <input type="text" name="items[{{ $it->id }}][keterangan]" value="{{ old("items.{$it->id}.keterangan", $it->keterangan ?: 'Diserahkan dalam kondisi baik') }}"
                                       class="w-full text-xs bg-white border border-slate-300 rounded p-1.5">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Submit Bar -->
        <div class="flex items-center justify-between pt-4 border-t border-slate-200">
            <a href="{{ route('supply.distribusi.show', $distribusi->id) }}" 
               class="px-5 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-semibold rounded-xl shadow-2xs transition-all">
                Batal
            </a>

            <button type="submit" 
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#094886] hover:bg-[#073868] text-white text-sm font-bold rounded-xl shadow-sm transition-all cursor-pointer">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span>Simpan Penyesuaian BAST</span>
            </button>
        </div>

    </form>

</div>
@endsection

