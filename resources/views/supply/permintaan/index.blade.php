@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-[26px] font-bold text-slate-900 tracking-tight">Permintaan Barang Persediaan (Supply)</h1>
            <p class="text-sm text-slate-500 mt-1">Daftar permohonan pengambilan barang persediaan oleh unit kerja dan pegawai BPS.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <!-- Link ke Katalog Persediaan -->
            <a href="{{ route('supply.inventory.index') }}" 
               class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 bg-white border border-slate-300 hover:border-slate-400 text-slate-700 text-xs sm:text-sm font-semibold rounded-lg shadow-2xs transition-all cursor-pointer">
                <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span>Katalog & Stok</span>
            </a>

            @if(!\App\Services\SatkerService::isViewingOtherSatker())
            <!-- Tombol Buat Permintaan Supply -->
            <a href="{{ route('supply.permintaan.create') }}" 
               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-[#094886] hover:bg-[#073868] text-white text-xs sm:text-sm font-semibold rounded-lg shadow-sm transition-all cursor-pointer">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Supply</span>
            </a>
            @else
            <span class="inline-flex items-center gap-1.5 px-3 py-2 bg-amber-50 text-amber-800 border border-amber-200 text-xs font-medium rounded-lg">
                <svg class="w-4 h-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span>Mode Lihat Saja</span>
            </span>
            @endif
        </div>
    </div>

    <!-- Metric Counter Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('supply.permintaan.index') }}" 
           class="bg-white rounded-xl border {{ empty($status) ? 'border-[#094886] ring-2 ring-blue-500/10' : 'border-slate-200/90' }} p-4 shadow-xs hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between text-xs font-bold text-slate-500 uppercase">
                <span>Total Permintaan</span>
                <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 mt-2">{{ $countSemua }}</div>
            <div class="text-xs text-slate-400 mt-1">Seluruh riwayat permohonan</div>
        </a>

        <a href="{{ route('supply.permintaan.index', ['status' => 'Menunggu']) }}" 
           class="bg-white rounded-xl border {{ $status === 'Menunggu' ? 'border-amber-500 ring-2 ring-amber-500/10' : 'border-slate-200/90' }} p-4 shadow-xs hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between text-xs font-bold text-amber-600 uppercase">
                <span>Menunggu Distribusi</span>
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
            </div>
            <div class="text-2xl font-extrabold text-amber-600 mt-2">{{ $countMenunggu }}</div>
            <div class="text-xs text-slate-400 mt-1">Perlu disetujui & didistribusikan</div>
        </a>

        <a href="{{ route('supply.permintaan.index', ['status' => 'Didistribusikan']) }}" 
           class="bg-white rounded-xl border {{ $status === 'Didistribusikan' ? 'border-emerald-500 ring-2 ring-emerald-500/10' : 'border-slate-200/90' }} p-4 shadow-xs hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between text-xs font-bold text-emerald-600 uppercase">
                <span>Sudah Didistribusikan</span>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            </div>
            <div class="text-2xl font-extrabold text-emerald-600 mt-2">{{ $countDidistribusikan }}</div>
            <div class="text-xs text-slate-400 mt-1">BAST telah terbit</div>
        </a>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
        <form method="GET" action="{{ route('supply.permintaan.index') }}">
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
                           placeholder="Cari nomor permintaan, nama pemohon, unit kerja, keperluan..."
                           class="w-full bg-white border border-slate-300 hover:border-slate-400 text-slate-700 text-sm rounded-lg pl-10 pr-9 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    @if($search)
                    <a href="{{ route('supply.permintaan.index', request()->except('search')) }}" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </a>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:flex items-center gap-3">
                    <div class="relative min-w-[170px]">
                        <select name="status" onchange="this.form.submit()" class="w-full appearance-none bg-white border border-slate-300 hover:border-slate-400 text-slate-700 text-sm rounded-lg px-3.5 py-2.5 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer">
                            <option value="">Semua Status</option>
                            <option value="Menunggu" {{ $status === 'Menunggu' ? 'selected' : '' }}>Menunggu Distribusi</option>
                            <option value="Didistribusikan" {{ $status === 'Didistribusikan' ? 'selected' : '' }}>Didistribusikan</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                    </div>

                    <div class="relative min-w-[170px]">
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
                        <th class="px-5 py-3.5">NOMOR & TANGGAL</th>
                        <th class="px-5 py-3.5">PEMOHON / UNIT KERJA</th>
                        <th class="px-5 py-3.5">KEPERLUAN</th>
                        <th class="px-5 py-3.5 text-center">JUMLAH ITEM</th>
                        <th class="px-5 py-3.5 text-center">STATUS</th>
                        <th class="px-5 py-3.5 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($permintaans as $index => $p)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-5 py-4 text-center text-xs font-medium text-slate-400">
                            {{ $permintaans->firstItem() + $index }}
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex flex-col">
                                <span class="font-mono text-xs font-bold text-[#094886]">{{ $p->nomor_permintaan }}</span>
                                <span class="text-xs text-slate-500 mt-0.5">
                                    {{ $p->tanggal_permintaan ? $p->tanggal_permintaan->translatedFormat('d F Y') : '-' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-900">{{ $p->nama_pemohon }}</span>
                                <span class="text-xs text-slate-600 mt-0.5">{{ $p->unit_kerja }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-xs text-slate-700 line-clamp-2 max-w-xs" title="{{ $p->keperluan }}">
                                {{ $p->keperluan }}
                            </div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-slate-100 text-slate-700">
                                {{ $p->items->count() }} barang
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($p->status === 'Didistribusikan')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    Didistribusikan
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Menunggu Distribusi
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('supply.permintaan.show', $p->id) }}" 
                                   class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#094886] hover:bg-[#073868] text-white text-xs font-semibold rounded-lg shadow-2xs transition-colors">
                                    <span>Detail</span>
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                </a>

                                @if($p->distribusi)
                                <a href="{{ route('supply.distribusi.show', $p->distribusi->id) }}" 
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-[#094886] text-xs font-bold rounded-lg border border-blue-200 transition-colors"
                                   title="Lihat BAST Terkait">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                    <span>BAST</span>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-slate-300 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                                </svg>
                                <span class="text-sm font-semibold text-slate-600">Belum ada permintaan supply barang</span>
                                <span class="text-xs text-slate-400 mt-1">Klik tombol Buat Permintaan Barang untuk memulai.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($permintaans->hasPages())
        <div class="px-5 py-3.5 border-t border-slate-100 flex items-center justify-between">
            <div class="text-xs text-slate-500">
                Menampilkan {{ $permintaans->firstItem() }} - {{ $permintaans->lastItem() }} dari {{ $permintaans->total() }} data
            </div>
            <div>
                {{ $permintaans->links() }}
            </div>
        </div>
        @endif
    </div>

</div>
@endsection
