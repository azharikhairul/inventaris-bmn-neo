@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="inventoryManager()">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-[26px] font-bold text-slate-900 tracking-tight">Katalog & Stok Persediaan (Supply)</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola daftar barang persediaan, mutasi barang masuk, dan ketersediaan stok kantor.</p>
        </div>
        <div class="flex items-center gap-2 sm:gap-2.5">
            <!-- Export CSV -->
            <a href="{{ route('supply.inventory.export', request()->query()) }}" 
               class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-3.5 py-2.5 bg-white border border-slate-300 hover:border-slate-400 text-slate-700 text-xs sm:text-sm font-semibold rounded-lg shadow-2xs transition-all cursor-pointer"
               title="Export Daftar Stok ke File CSV">
                <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Export CSV</span>
            </a>

            @if(\App\Services\SatkerService::isViewingOtherSatker())
            <div class="inline-flex items-center gap-1.5 px-3 py-2 bg-amber-50 text-amber-800 text-xs font-semibold rounded-xl border border-amber-200">
                <svg class="w-4 h-4 text-amber-600 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span>Mode Lihat Saja</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Metric Counter Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Jenis Barang -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Item</span>
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-[#094886] flex items-center justify-center">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2">{{ number_format($totalItems) }}</div>
            <div class="text-xs text-slate-400 mt-1">Jenis barang terdaftar</div>
        </div>

        <!-- Total Unit Stok -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Fisik Stok</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600 mt-2">{{ number_format($totalStok) }}</div>
            <div class="text-xs text-slate-400 mt-1">Kuantitas di gudang</div>
        </div>

        <!-- Stok Menipis -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Stok Menipis</span>
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    </svg>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-amber-600 mt-2">{{ number_format($totalMenipis) }}</div>
            <div class="text-xs text-slate-400 mt-1">Perlu pengadaan ulang</div>
        </div>

        <!-- Stok Habis -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Stok Habis</span>
                <span class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                    </svg>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-rose-600 mt-2">{{ number_format($totalHabis) }}</div>
            <div class="text-xs text-slate-400 mt-1">Stok kosong (0 unit)</div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
        <form method="GET" action="{{ route('supply.inventory.index') }}">
            @if(request('per_page'))
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
            @endif

            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <!-- Search Input -->
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
                           placeholder="Cari kode barang, nama barang, spesifikasi..."
                           class="w-full bg-white border border-slate-300 hover:border-slate-400 text-slate-700 text-sm rounded-lg pl-10 pr-9 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    @if($search)
                    <a href="{{ route('supply.inventory.index', request()->except('search')) }}" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </a>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:flex items-center gap-3">
                    <!-- Kategori Dropdown -->
                    <div class="relative min-w-[180px]">
                        <select name="kategori" onchange="this.form.submit()" class="w-full appearance-none bg-white border border-slate-300 hover:border-slate-400 text-slate-700 text-sm rounded-lg px-3.5 py-2.5 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer">
                            <option value="">Semua Kategori</option>
                            @foreach($kategoris as $kat)
                                <option value="{{ $kat }}" {{ $kategori === $kat ? 'selected' : '' }}>{{ $kat }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                    </div>

                    <!-- Status Stok Filter -->
                    <div class="relative min-w-[160px]">
                        <select name="status_stok" onchange="this.form.submit()" class="w-full appearance-none bg-white border border-slate-300 hover:border-slate-400 text-slate-700 text-sm rounded-lg px-3.5 py-2.5 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer">
                            <option value="">Semua Status Stok</option>
                            <option value="tersedia" {{ $statusStok === 'tersedia' ? 'selected' : '' }}>Stok Aman / Tersedia</option>
                            <option value="menipis" {{ $statusStok === 'menipis' ? 'selected' : '' }}>Stok Menipis</option>
                            <option value="habis" {{ $statusStok === 'habis' ? 'selected' : '' }}>Stok Habis</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
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
                        <th class="px-5 py-3.5">KODE & NAMA BARANG</th>
                        <th class="px-5 py-3.5">KATEGORI</th>
                        <th class="px-5 py-3.5 text-center">STOK SAAT INI</th>
                        <th class="px-5 py-3.5 text-center">MIN. STOK</th>
                        <th class="px-5 py-3.5 text-center">STATUS</th>
                        <th class="px-5 py-3.5 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($supplies as $index => $item)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-5 py-4 text-center text-xs font-medium text-slate-400">
                            {{ $supplies->firstItem() + $index }}
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex flex-col">
                                <span class="font-mono text-xs font-bold text-[#094886]">{{ $item->kode_barang }}</span>
                                <span class="font-bold text-slate-900 mt-0.5">{{ $item->nama_barang }}</span>
                                @if($item->spesifikasi)
                                    <span class="text-xs text-slate-500 mt-0.5 line-clamp-1">{{ $item->spesifikasi }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700">
                                {{ $item->kategori }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <div class="inline-flex items-baseline gap-1">
                                <span class="text-base font-extrabold {{ $item->stok <= 0 ? 'text-rose-600' : ($item->stok <= $item->stok_minimum ? 'text-amber-600' : 'text-slate-900') }}">
                                    {{ $item->stok }}
                                </span>
                                <span class="text-xs text-slate-500 font-medium">{{ $item->satuan }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-center text-xs text-slate-500 font-medium">
                            {{ $item->stok_minimum }} {{ $item->satuan }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($item->stok <= 0)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    Habis
                                </span>
                            @elseif($item->stok <= $item->stok_minimum)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Menipis
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Tersedia
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">

                                <!-- Riwayat Mutasi -->
                                <a href="{{ route('supply.inventory.mutasi', $item->id) }}" 
                                   class="p-1.5 text-slate-500 hover:text-[#094886] hover:bg-slate-100 rounded-lg transition-colors"
                                   title="Riwayat Mutasi Stok">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                    </svg>
                                </a>

                                @if(!\App\Services\SatkerService::isViewingOtherSatker())
                                <!-- Edit Barang -->
                                <button type="button" 
                                        @click="openEditModal({{ json_encode($item) }})"
                                        class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer"
                                        title="Edit Data Barang">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                </button>

                                <!-- Hapus Barang -->
                                <form action="{{ route('supply.inventory.destroy', $item->id) }}" method="POST" 
                                      onsubmit="return confirm('Yakin ingin menghapus barang persediaan {{ addslashes($item->nama_barang) }}?')" 
                                      class="inline m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                            title="Hapus Barang">
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
                                    <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <span class="text-sm font-semibold text-slate-600">Tidak ada barang persediaan yang ditemukan</span>
                                <span class="text-xs text-slate-400 mt-1">Sesuaikan kata kunci atau filter pencarian Anda.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($supplies->hasPages())
        <div class="px-5 py-3.5 border-t border-slate-100 flex items-center justify-between">
            <div class="text-xs text-slate-500">
                Menampilkan {{ $supplies->firstItem() }} - {{ $supplies->lastItem() }} dari {{ $supplies->total() }} barang
            </div>
            <div>
                {{ $supplies->links() }}
            </div>
        </div>
        @endif
    </div>

    <!-- Modal: Edit Data Barang -->
    <div x-show="showEditModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-lg overflow-hidden"
             @click.outside="showEditModal = false">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="font-bold text-slate-900 text-base">Edit Data Barang Persediaan</h3>
                <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <form :action="'/supply/inventory/' + form.id" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="_method" value="PUT">

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kode Barang <span class="text-rose-500">*</span></label>
                        <input type="text" name="kode_barang" x-model="form.kode_barang" required placeholder="Contoh: ATK-016" 
                               class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kategori <span class="text-rose-500">*</span></label>
                        <input type="text" name="kategori" x-model="form.kategori" required placeholder="Contoh: ATK" 
                               list="kategoriList"
                               class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        <datalist id="kategoriList">
                            <option value="Alat Tulis Kantor (ATK)">
                            <option value="Kertas & Dokumen">
                            <option value="Komputer & IT">
                            <option value="Alat Kebersihan">
                            <option value="Lainnya">
                        </datalist>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Barang <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_barang" x-model="form.nama_barang" required placeholder="Nama lengkap barang persediaan..." 
                           class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Satuan <span class="text-rose-500">*</span></label>
                        <input type="text" name="satuan" x-model="form.satuan" required placeholder="Rim, Pcs, Box..." 
                               class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Stok (Terkunci)</label>
                        <input type="number" name="stok" x-model="form.stok" disabled
                               class="w-full bg-slate-100 text-slate-400 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Min. Stok</label>
                        <input type="number" name="stok_minimum" x-model="form.stok_minimum" min="0" placeholder="5" 
                               class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Spesifikasi / Deskripsi</label>
                    <textarea name="spesifikasi" x-model="form.spesifikasi" rows="2" placeholder="Ukuran, merk, peruntukan..." 
                              class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Batal</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold bg-[#094886] hover:bg-[#073868] text-white rounded-lg shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function inventoryManager() {
    return {
        showEditModal: false,
        form: {
            id: null,
            kode_barang: '',
            nama_barang: '',
            kategori: 'Alat Tulis Kantor (ATK)',
            satuan: 'Pcs',
            stok: 0,
            stok_minimum: 5,
            spesifikasi: '',
            harga_estimasi: 0
        },

        openEditModal(item) {
            this.form = {
                id: item.id,
                kode_barang: item.kode_barang,
                nama_barang: item.nama_barang,
                kategori: item.kategori,
                satuan: item.satuan,
                stok: item.stok,
                stok_minimum: item.stok_minimum,
                spesifikasi: item.spesifikasi || '',
                harga_estimasi: item.harga_estimasi || 0
            };
            this.showEditModal = true;
        }
    };
}
</script>
@endsection
