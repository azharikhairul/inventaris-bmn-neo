@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    showUploadModal: false,
    uploadAction: '',
    nomorBast: '',
    penerima: '',
    unitKerja: '',
    openUpload(id, no, pen, unit) {
        this.uploadAction = '{{ url('supply/distribusi') }}/' + id + '/upload-ttd';
        this.nomorBast = no;
        this.penerima = pen;
        this.unitKerja = unit;
        this.showUploadModal = true;
    }
}">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl sm:text-[26px] font-bold text-slate-900 tracking-tight">Distribusi Barang Persediaan</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Pengeluaran Stok & BAST
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Pencatatan pengeluaran barang persediaan ke pegawai/unit kerja beserta penerbitan dan pengunggahan berkas BAST.</p>
        </div>
        @if(!\App\Services\SatkerService::isViewingOtherSatker())
        <div>
            <!-- Tombol Distribusi Baru -->
            <a href="{{ route('supply.distribusi.create') }}" 
               class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-[#094886] hover:bg-[#073868] text-white text-xs sm:text-sm font-semibold rounded-lg shadow-sm transition-all cursor-pointer"
               title="Keluarkan barang dari stok gudang dan catat transaksi distribusi">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>+ Distribusi</span>
            </a>
        </div>
        @endif
    </div>

    <!-- Status Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Distribusi -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="flex items-center justify-between text-xs font-bold text-slate-500 uppercase">
                <span>Total Distribusi</span>
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-[#094886] flex items-center justify-center">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="3" width="15" height="13"></rect>
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                        <circle cx="5.5" cy="18.5" r="2.5"></circle>
                        <circle cx="18.5" cy="18.5" r="2.5"></circle>
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 mt-2">{{ number_format($totalDistribusi) }}</div>
            <div class="text-xs text-slate-400 mt-1">Kali pengeluaran barang</div>
        </div>

        <!-- Card 2: Total Item Disalurkan -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="flex items-center justify-between text-xs font-bold text-slate-500 uppercase">
                <span>Item Disalurkan</span>
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-extrabold text-indigo-700 mt-2">{{ number_format($totalItemDisalurkan) }}</div>
            <div class="text-xs text-slate-400 mt-1">Total unit barang diserahkan</div>
        </div>

        <!-- Card 3: Menunggu TTD BAST -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="flex items-center justify-between text-xs font-bold text-amber-600 uppercase">
                <span>Menunggu TTD BAST</span>
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-extrabold text-amber-700 mt-2">{{ number_format($countMenungguTtd) }}</div>
            <div class="text-xs text-slate-400 mt-1">Perlu diunggah scan TTD</div>
        </div>

        <!-- Card 4: Selesai / Sudah TTD -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
            <div class="flex items-center justify-between text-xs font-bold text-emerald-600 uppercase">
                <span>Selesai (Sudah TTD)</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <polyline points="9 15 11 17 15 13"></polyline>
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-extrabold text-emerald-700 mt-2">{{ number_format($countSelesai) }}</div>
            <div class="text-xs text-slate-400 mt-1">Arsip berkas lengkap</div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
        <form method="GET" action="{{ route('supply.distribusi.index') }}">
            @if(request('per_page'))
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
            @endif

            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <div class="relative flex-1 min-w-0">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </div>
                    <input type="text" 
                           name="search"
                           value="{{ $search }}"
                           placeholder="Cari nomor BAST, penerima, unit kerja, nama barang, keperluan..."
                           class="w-full bg-white border border-slate-300 hover:border-slate-400 text-slate-700 text-sm rounded-lg pl-10 pr-9 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    @if($search)
                    <a href="{{ route('supply.distribusi.index', request()->except('search')) }}" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </a>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 md:flex items-center gap-3">
                    <!-- Filter Status BAST -->
                    <div class="relative min-w-[160px]">
                        <select name="status" onchange="this.form.submit()" 
                                class="w-full bg-white border border-slate-300 hover:border-slate-400 text-slate-700 text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer">
                            <option value="">Semua Status BAST</option>
                            <option value="Menunggu TTD" {{ $status === 'Menunggu TTD' ? 'selected' : '' }}>Menunggu TTD ({{ $countMenungguTtd }})</option>
                            <option value="Selesai" {{ $status === 'Selesai' ? 'selected' : '' }}>Selesai (Sudah TTD) ({{ $countSelesai }})</option>
                        </select>
                    </div>

                    <div class="relative min-w-[150px]">
                        <input type="date" 
                                name="date" 
                                value="{{ $date }}" 
                                onchange="this.form.submit()" 
                                class="w-full bg-white border border-slate-300 hover:border-slate-400 text-slate-700 text-sm rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer">
                    </div>

                    <button type="submit" class="px-4 py-2.5 bg-[#094886] hover:bg-[#073868] text-white text-sm font-semibold rounded-lg transition-colors cursor-pointer">
                        Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-xs font-bold text-slate-600 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5 w-14 text-center">NO</th>
                        <th class="px-5 py-3.5">TANGGAL DISTRIBUSI</th>
                        <th class="px-5 py-3.5">PENERIMA & UNIT KERJA</th>
                        <th class="px-5 py-3.5">BARANG YANG DISERAHKAN</th>
                        <th class="px-5 py-3.5">KEPERLUAN / KEGIATAN</th>
                        <th class="px-5 py-3.5 text-center">DOKUMEN BAST</th>
                        <th class="px-5 py-3.5 text-center min-w-[200px]">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($distribusis as $index => $d)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-5 py-4 text-center text-xs font-medium text-slate-400">
                            {{ $distribusis->firstItem() + $index }}
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="font-bold text-slate-900 text-xs sm:text-sm">
                                {{ $d->tanggal_bast ? $d->tanggal_bast->translatedFormat('d F Y') : '-' }}
                            </div>
                            <span class="text-[11px] text-slate-500">
                                Petugas: {{ $d->pihak_pertama_nama }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-bold text-slate-900 text-xs sm:text-sm">{{ $d->pihak_kedua_nama }}</div>
                            <div class="text-xs text-slate-600 mt-0.5">{{ $d->pihak_kedua_unit_kerja }}</div>
                            @if($d->pihak_kedua_jabatan)
                                <div class="text-[11px] text-slate-400">{{ $d->pihak_kedua_jabatan }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="space-y-1 max-w-xs">
                                @foreach($d->items->take(2) as $it)
                                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-slate-100 rounded text-xs text-slate-700 font-medium mr-1 mb-1">
                                    <span class="font-semibold text-slate-900">{{ $it->nama_barang }}</span>
                                    <span class="text-slate-500">({{ $it->jumlah }} {{ $it->satuan }})</span>
                                </div>
                                @endforeach
                                @if($d->items->count() > 2)
                                    <span class="text-[11px] font-semibold text-[#094886] block">
                                        +{{ $d->items->count() - 2 }} barang lainnya
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-xs text-slate-700 line-clamp-2">
                                {{ $d->kegiatan_keperluan ?: 'Operasional Kantor' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center whitespace-nowrap">
                            <div class="flex flex-col items-center gap-1">
                                <a href="{{ route('supply.distribusi.show', $d->id) }}" 
                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-blue-50 text-[#094886] hover:bg-blue-100 border border-blue-200 transition-colors"
                                   title="Lihat Dokumen BAST #{{ $d->nomor_bast }}">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                    <span>{{ $d->nomor_bast }}</span>
                                </a>

                                @if($d->status === 'Selesai' || $d->file_bast_ttd)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        Sudah TTD
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Menunggu TTD
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                <!-- Tombol Lihat BAST -->
                                <a href="{{ route('supply.distribusi.show', $d->id) }}" 
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-[#094886] hover:bg-[#073868] text-white text-xs font-semibold rounded-lg shadow-2xs transition-colors"
                                   title="Lihat Dokumen BAST & Rincian">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    <span>Lihat</span>
                                </a>

                                <!-- Tombol Unduh PDF BAST -->
                                <a href="{{ route('supply.distribusi.pdf', [$d->id, 'download' => 1]) }}" 
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold rounded-lg shadow-2xs transition-colors"
                                   title="Unduh BAST format PDF untuk dicetak dan ditandatangani">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                    <span>PDF</span>
                                </a>

                                <!-- Tombol Scan / Upload BAST -->
                                @if($d->status === 'Selesai' || $d->file_bast_ttd)
                                    <a href="{{ $d->file_url }}" target="_blank" 
                                       class="inline-flex items-center gap-1 px-2 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-semibold rounded-lg shadow-2xs transition-colors"
                                       title="Buka Berkas Scan BAST yang sudah ditandatangani">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        <span>Scan</span>
                                    </a>

                                    @if(!\App\Services\SatkerService::isViewingOtherSatker())
                                    <button type="button" 
                                            @click="openUpload({{ $d->id }}, '{{ $d->nomor_bast }}', '{{ addslashes($d->pihak_kedua_nama) }}', '{{ addslashes($d->pihak_kedua_unit_kerja) }}')"
                                            class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer"
                                            title="Unggah Ulang / Ganti Berkas Scan BAST">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                    </button>
                                    @endif
                                @else
                                    @if(!\App\Services\SatkerService::isViewingOtherSatker())
                                    <button type="button" 
                                            @click="openUpload({{ $d->id }}, '{{ $d->nomor_bast }}', '{{ addslashes($d->pihak_kedua_nama) }}', '{{ addslashes($d->pihak_kedua_unit_kerja) }}')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 text-xs font-semibold rounded-lg shadow-2xs transition-colors cursor-pointer"
                                            title="Unggah berkas scan BAST yang telah ditandatangani">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                        <span>Upload</span>
                                    </button>
                                    @endif
                                @endif

                                @if(!\App\Services\SatkerService::isViewingOtherSatker())
                                <!-- Tombol Hapus / Batalkan Distribusi -->
                                <form action="{{ route('supply.distribusi.destroy', $d->id) }}" method="POST" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin membatalkan distribusi ini? Stok barang akan dikembalikan ke gudang.');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer" title="Batalkan Distribusi (Kembalikan Stok)">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-slate-300 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="1" y="3" width="15" height="13"></rect>
                                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                </svg>
                                <span class="text-sm font-semibold text-slate-600">Belum ada data distribusi barang</span>
                                <span class="text-xs text-slate-400 mt-1">Gunakan tombol "+ Distribusi" untuk mencatat pengeluaran barang persediaan ke unit kerja.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($distribusis->hasPages())
        <div class="px-5 py-3.5 border-t border-slate-100 flex items-center justify-between">
            <div class="text-xs text-slate-500">
                Menampilkan {{ $distribusis->firstItem() }} - {{ $distribusis->lastItem() }} dari {{ $distribusis->total() }} distribusi
            </div>
            <div>
                {{ $distribusis->links() }}
            </div>
        </div>
        @endif
    </div>

    <!-- MODAL UPLOAD SCAN BAST TERTANDATANGAN -->
    <div x-show="showUploadModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <!-- Background Backdrop -->
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" 
                 @click="showUploadModal = false"></div>

            <!-- Modal Card -->
            <div class="relative inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl border border-slate-200">
                
                <div class="flex items-start justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Unggah Scan BAST Distribusi</h3>
                            <p class="text-xs text-slate-500 font-mono mt-0.5" x-text="nomorBast"></p>
                        </div>
                    </div>
                    <button type="button" @click="showUploadModal = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>

                <div class="my-4 p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-600 space-y-1">
                    <div>Penerima: <strong class="text-slate-900" x-text="penerima"></strong> (<span x-text="unitKerja"></span>)</div>
                    <div class="text-[11px] text-slate-500">Pastikan dokumen BAST telah ditandatangani basah oleh kedua pihak sebelum diunggah.</div>
                </div>

                <form :action="uploadAction" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Pilih Berkas Scan BAST (PDF, JPG, PNG - Maks 10MB) <span class="text-rose-500">*</span>
                        </label>
                        <input type="file" name="file_bast_ttd" accept=".pdf,.jpg,.jpeg,.png" required
                               class="w-full text-xs text-slate-700 bg-white border border-slate-300 rounded-lg p-2.5 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#094886] hover:file:bg-blue-100 cursor-pointer">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Catatan Arsip (Opsional)
                        </label>
                        <textarea name="catatan" rows="2" placeholder="Catatan tambahan mengenai penyerahan atau kondisi barang..." 
                                  class="w-full text-xs text-slate-700 bg-white border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                        <button type="button" @click="showUploadModal = false" 
                                class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors cursor-pointer">
                            Unggah Scan BAST
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>
@endsection
