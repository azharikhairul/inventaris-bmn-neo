@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-20" x-data="distribusiCreateForm()">

    <!-- Header Title & Back Link -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl sm:text-[26px] font-bold text-slate-900 tracking-tight">Buat Berita Acara Serah Terima (BAST)</h1>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Distribusi Supply
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Lengkapi data penyerahan barang persediaan kepada unit kerja pemohon.</p>
        </div>
        <a href="{{ route('supply.distribusi.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 hover:bg-slate-50 px-3.5 py-2 rounded-xl transition-colors self-start sm:self-auto shadow-2xs">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            <span>Kembali ke Daftar</span>
        </a>
    </div>

    <!-- Banner Info Jika Terhubung Realisasi -->
    @if($realisasi)
    <div class="p-4 bg-emerald-50/80 border border-emerald-200/90 rounded-2xl flex items-start gap-3 shadow-2xs">
        <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
        </div>
        <div class="flex-1 min-w-0">
            <h4 class="text-xs font-bold text-emerald-900 uppercase tracking-wider">Distribusi Dari Realisasi Supply Selesai</h4>
            <div class="text-sm font-bold text-emerald-950 mt-0.5">{{ $realisasi->pekerjaan }}</div>
            <div class="flex flex-wrap items-center gap-3 text-xs text-emerald-800 mt-1">
                <span>No. FPA: <strong class="font-mono">{{ $realisasi->no_fpa ?? '-' }}</strong></span>
                <span>•</span>
                <span>Total Realisasi: <strong>Rp {{ number_format($realisasi->total_realisasi ?? 0, 0, ',', '.') }}</strong></span>
            </div>
        </div>
    </div>
    @endif

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

    <form action="{{ route('supply.distribusi.store') }}" method="POST" class="space-y-6">
        @csrf
        @if($realisasi)
            <input type="hidden" name="realisasi_id" value="{{ $realisasi->id }}">
            <input type="hidden" name="permintaan_id" value="{{ $realisasi->permintaan_id }}">
        @elseif($permintaan)
            <input type="hidden" name="permintaan_id" value="{{ $permintaan->id }}">
        @endif

        <!-- Card: Tanggal & Keperluan -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-xs space-y-4">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <span>Informasi Penyerahan & BAST</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tanggal Serah Terima / BAST <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal_bast" required 
                           value="{{ old('tanggal_bast', date('Y-m-d')) }}"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-medium">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Keperluan / Kegiatan
                    </label>
                    <input type="text" name="kegiatan_keperluan" 
                           placeholder="Contoh: Kebutuhan operasional kantor triwulan II"
                           value="{{ old('kegiatan_keperluan', $realisasi->pekerjaan ?? ($permintaan->pekerjaan ?? '')) }}"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                </div>
            </div>
        </div>

        <!-- Card: Pihak Penerima Barang -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-xs space-y-4">
            <div class="pb-2 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Pihak Penerima Barang</span>
                </h3>
                <span class="text-[10px] font-bold text-emerald-600">PEMOHON / PENERIMA</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Nama Penerima <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="pihak_kedua_nama" required
                           value="{{ old('pihak_kedua_nama', '') }}"
                           placeholder="Nama pegawai yang menerima..."
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-semibold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Unit Kerja / Seksi / Bagian <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="pihak_kedua_unit_kerja" required
                           value="{{ old('pihak_kedua_unit_kerja', 'Bagian Umum') }}"
                           placeholder="Contoh: Bagian Umum / Tim Distribusi"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-medium">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        NIP Penerima
                    </label>
                    <input type="text" name="pihak_kedua_nip" 
                           value="{{ old('pihak_kedua_nip', '') }}"
                           placeholder="NIP penerima..."
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Jabatan Penerima
                    </label>
                    <input type="text" name="pihak_kedua_jabatan" 
                           value="{{ old('pihak_kedua_jabatan', 'Staff') }}"
                           placeholder="Jabatan..."
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                </div>
            </div>
        </div>

        <!-- Card: Daftar Barang Yang Diserahkan -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Daftar Rincian Barang Yang Diserahkan</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Pastikan kuantitas dan satuan barang sesuai dengan fisik yang diserahkan.</p>
                </div>
                <button type="button" @click="addItem()" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-xl transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Tambah Baris Barang</span>
                </button>
            </div>

            <div class="border border-slate-200/90 rounded-xl overflow-x-auto min-h-[360px] pb-28">
                <table class="w-full min-w-[640px] text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4">Nama Barang Persediaan <span class="text-rose-500">*</span></th>
                            <th class="py-3 px-4 w-28 text-center">Jumlah <span class="text-rose-500">*</span></th>
                            <th class="py-3 px-4 w-32">Satuan <span class="text-rose-500">*</span></th>
                            <th class="py-3 px-4">Keterangan</th>
                            <th class="py-3 px-4 w-14 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        <template x-for="(item, idx) in items" :key="idx">
                            <tr class="hover:bg-slate-50 transition-colors align-top">
                                <td class="py-3.5 px-4 text-center text-slate-400 font-mono text-xs" x-text="idx + 1"></td>
                                
                                <!-- Kolom Nama Barang dengan Dropdown Semua Supply Persediaan -->
                                <td class="py-2.5 px-4" :class="item.openDropdown ? 'relative z-40' : 'relative z-10'" @click.outside="item.openDropdown = false">
                                    <input type="hidden" :name="'items[' + idx + '][supply_id]'" x-model="item.supply_id">
                                    <div class="relative flex items-center">
                                        <input type="text" :name="'items[' + idx + '][nama_barang]'" x-model="item.nama_barang" required
                                               @focus="item.openDropdown = true"
                                               @input="onItemNameInput(idx)"
                                               placeholder="Pilih atau cari barang persediaan..."
                                               class="w-full bg-transparent border-0 border-b border-transparent focus:border-emerald-500 px-1 py-1 pr-6 text-sm font-semibold focus:outline-none focus:ring-0"
                                               :class="item.sisa_stok !== undefined && item.sisa_stok !== null && item.sisa_stok <= 0 ? 'text-rose-600 font-bold' : 'text-slate-800'">
                                        <button type="button" 
                                                @click="item.openDropdown = !item.openDropdown"
                                                class="absolute right-0 text-slate-400 hover:text-slate-600 p-1 cursor-pointer"
                                                tabindex="-1"
                                                title="Tampilkan semua daftar supply persediaan">
                                            <svg class="w-4 h-4 transition-transform" :class="{'rotate-180': item.openDropdown}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="6 9 12 15 18 9"></polyline>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Indikator Status Sisa Stok Barang Terpilih -->
                                    <template x-if="item.sisa_stok !== undefined && item.sisa_stok !== null">
                                        <div>
                                            <template x-if="item.sisa_stok <= 0">
                                                <div class="text-[11px] text-rose-600 font-bold px-1 mt-1 flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                                    <span>(sisa stok = 0)</span>
                                                    <span class="text-rose-500 font-normal">&bull; Stok persediaan sedang habis</span>
                                                </div>
                                            </template>
                                            <template x-if="item.sisa_stok > 0">
                                                <div class="text-[11px] text-emerald-600 font-medium px-1 mt-0.5 flex items-center gap-1">
                                                    <span>Sisa stok gudang:</span>
                                                    <strong x-text="item.sisa_stok + ' ' + (item.satuan || '')"></strong>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Dropdown Pilihan Semua Supply Persediaan -->
                                    <div x-show="item.openDropdown" 
                                         x-cloak
                                         class="absolute z-50 left-0 top-full mt-1 w-80 sm:w-96 bg-white rounded-xl border border-slate-200 shadow-2xl overflow-hidden max-h-60 overflow-y-auto divide-y divide-slate-100 text-xs">
                                        <div class="p-2.5 bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between border-b border-slate-100">
                                            <span>Daftar Stok Persediaan</span>
                                            <span class="text-[10px] text-slate-400 font-normal">Pilih barang</span>
                                        </div>
                                        <template x-for="s in getFilteredSupplies(item)" :key="s.id">
                                            <div @click="selectSupply(idx, s)"
                                                 class="p-2.5 hover:bg-slate-50 cursor-pointer flex items-center justify-between gap-2.5 transition-colors"
                                                 :class="s.stok <= 0 ? 'bg-rose-50/40 hover:bg-rose-50/80' : ''">
                                                <div class="min-w-0 flex-1">
                                                    <div class="font-bold truncate text-sm"
                                                         :class="s.stok <= 0 ? 'text-rose-600 font-bold' : 'text-slate-800'"
                                                         x-text="s.nama_barang"></div>
                                                    <div class="flex items-center gap-2 mt-0.5 text-xs">
                                                        <template x-if="s.kode_barang">
                                                            <span class="font-mono text-[10px] text-slate-400" x-text="'[' + s.kode_barang + ']'"></span>
                                                        </template>
                                                        <span class="text-slate-400 text-[11px]" x-text="s.kategori"></span>
                                                        <span class="font-semibold text-xs ml-auto"
                                                              :class="s.stok <= 0 ? 'text-rose-600 font-bold' : 'text-emerald-700'"
                                                              x-text="s.stok <= 0 ? '(sisa stok = 0)' : '(Sisa Stok: ' + s.stok + ' ' + (s.satuan || '') + ')'"></span>
                                                    </div>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                                          :class="s.stok <= 0 ? 'bg-rose-100 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'"
                                                          x-text="s.stok <= 0 ? 'Habis (0)' : 'Pilih'"></span>
                                                </div>
                                            </div>
                                        </template>
                                        <template x-if="getFilteredSupplies(item).length === 0">
                                            <div class="p-4 text-center text-slate-400 text-xs">
                                                Barang tidak ditemukan dalam stok persediaan.
                                            </div>
                                        </template>
                                    </div>
                                </td>

                                <td class="py-2.5 px-4 text-center">
                                    <input type="number" min="1" :name="'items[' + idx + '][jumlah]'" x-model.number="item.jumlah" required
                                           class="w-full bg-transparent border-0 text-center text-sm font-bold text-slate-800 focus:outline-none focus:ring-0"
                                           :class="(item.sisa_stok !== undefined && item.sisa_stok !== null && (item.jumlah > item.sisa_stok || item.sisa_stok <= 0)) ? 'text-rose-600 font-black' : ''">
                                    <template x-if="item.sisa_stok !== undefined && item.sisa_stok !== null && item.sisa_stok <= 0">
                                        <div class="text-[10px] text-rose-600 font-bold mt-0.5">Stok habis (0)!</div>
                                    </template>
                                    <template x-if="item.sisa_stok !== undefined && item.sisa_stok !== null && item.sisa_stok > 0 && item.jumlah > item.sisa_stok">
                                        <div class="text-[10px] text-rose-600 font-bold mt-0.5">Melebihi stok!</div>
                                    </template>
                                </td>
                                <td class="py-2.5 px-4">
                                    <input type="text" :name="'items[' + idx + '][satuan]'" x-model="item.satuan" required
                                           placeholder="rim / buah / kotak"
                                           class="w-full bg-transparent border-0 border-b border-transparent focus:border-emerald-500 px-1 py-1 text-sm text-slate-700 focus:outline-none focus:ring-0">
                                </td>
                                <td class="py-2.5 px-4">
                                    <input type="text" :name="'items[' + idx + '][keterangan]'" x-model="item.keterangan"
                                           placeholder="Keterangan..."
                                           class="w-full bg-transparent border-0 border-b border-transparent focus:border-emerald-500 px-1 py-1 text-xs text-slate-600 focus:outline-none focus:ring-0">
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    <button type="button" @click="removeItem(idx)" :disabled="items.length <= 1"
                                            class="text-slate-400 hover:text-rose-600 p-1 rounded-lg transition-colors cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bottom Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('supply.distribusi.index') }}" 
               class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors">
                Batal
            </a>
            <button type="submit" 
                    class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold text-white bg-[#094886] hover:bg-[#073868] active:scale-[0.99] rounded-xl shadow-md hover:shadow-lg transition-all cursor-pointer">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                <span>Terbitkan Berita Acara (BAST)</span>
            </button>
        </div>
    </form>
</div>

<script>
function distribusiCreateForm() {
    const catalog = {!! json_encode($supplies ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!};
    return {
        items: {!! json_encode($prefilledItems ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!},
        init() {
            this.items.forEach((it, idx) => {
                it.openDropdown = false;
                it.isTyping = false;
                if (it.nama_barang) {
                    const val = (it.nama_barang || '').toLowerCase().trim();
                    const matched = catalog.find(s => (s.nama_barang || '').toLowerCase().trim() === val);
                    if (matched) {
                        it.supply_id = matched.id;
                        it.sisa_stok = matched.stok;
                        if (!it.satuan || it.satuan === 'buah' || it.satuan === 'unit') {
                            it.satuan = matched.satuan;
                        }
                    }
                }
            });
        },
        onItemNameInput(idx) {
            const it = this.items[idx];
            it.isTyping = true;
            it.openDropdown = true;
            const val = (it.nama_barang || '').toLowerCase().trim();
            const matched = catalog.find(s => (s.nama_barang || '').toLowerCase().trim() === val);
            if (matched) {
                it.supply_id = matched.id;
                it.sisa_stok = matched.stok;
                if (!it.satuan || it.satuan === 'buah' || it.satuan === 'unit') {
                    it.satuan = matched.satuan;
                }
            } else {
                it.supply_id = null;
                it.sisa_stok = undefined;
            }
        },
        getFilteredSupplies(item) {
            if (item.supply_id && !item.isTyping) {
                return catalog;
            }
            const q = (item.nama_barang || '').toLowerCase().trim();
            if (!q) {
                return catalog;
            }
            return catalog.filter(s => {
                return (s.nama_barang || '').toLowerCase().includes(q) ||
                       (s.kategori || '').toLowerCase().includes(q) ||
                       (s.kode_barang || '').toLowerCase().includes(q);
            });
        },
        selectSupply(idx, s) {
            const it = this.items[idx];
            it.supply_id = s.id;
            it.nama_barang = s.nama_barang;
            it.satuan = s.satuan || it.satuan;
            it.sisa_stok = s.stok;
            it.spesifikasi = s.spesifikasi || s.kategori || '';
            it.isTyping = false;
            it.openDropdown = false;
        },
        addItem() {
            this.items.push({
                supply_id: null,
                nama_barang: '',
                spesifikasi: '',
                jumlah: 1,
                satuan: 'buah',
                keterangan: '',
                sisa_stok: undefined,
                openDropdown: false,
                isTyping: false
            });
        },
        removeItem(idx) {
            if (this.items.length > 1) {
                this.items.splice(idx, 1);
            }
        }
    };
}
</script>
@endsection
