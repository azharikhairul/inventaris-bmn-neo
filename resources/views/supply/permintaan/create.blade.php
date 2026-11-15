@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-16" x-data="supplyPermintaanForm({{ json_encode($supplies) }})">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('supply.permintaan.index') }}" 
               class="p-2 text-slate-400 hover:text-slate-600 hover:bg-white rounded-lg border border-slate-200 shadow-2xs transition-all">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Buat Permintaan Barang Persediaan</h1>
                <p class="text-sm text-slate-500 mt-0.5">Formulir pengajuan pengambilan barang persediaan / ATK BPS.</p>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl shadow-xs">
        <div class="font-bold text-sm mb-1">Terdapat kesalahan pengisian:</div>
        <ul class="list-disc list-inside text-xs space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('supply.permintaan.store') }}" method="POST" @submit="validateBeforeSubmit($event)">
        @csrf

        <!-- Card: Data Pemohon & Keperluan -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-xs space-y-5 mb-6">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#094886]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>Informasi Permintaan & Pemohon</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Nomor Permintaan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nomor Permintaan <span class="text-slate-400 font-normal">(Otomatis)</span>
                    </label>
                    <input type="text" value="{{ $nomorBaru }}" readonly 
                           class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm font-mono font-bold text-slate-700">
                </div>

                <!-- Tanggal Permintaan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tanggal Permintaan <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal_permintaan" value="{{ old('tanggal_permintaan', date('Y-m-d')) }}" required 
                           class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Unit Kerja -->
                <div class="sm:col-span-1">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Unit Kerja / Tim <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="unit_kerja" list="unitKerjaDatalist" required 
                           value="{{ old('unit_kerja', 'Bagian Umum') }}" placeholder="Pilih atau ketik unit kerja..."
                           class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    <datalist id="unitKerjaDatalist">
                        @foreach($unitKerjaList as $uk)
                            <option value="{{ $uk }}">
                        @endforeach
                    </datalist>
                </div>

                <!-- Nama Pemohon -->
                <div class="sm:col-span-1">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nama Pemohon <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama_pemohon" required 
                           value="{{ old('nama_pemohon', auth()->user()->name ?? auth()->user()->username ?? '') }}" placeholder="Nama lengkap pemohon..." 
                           class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>

                <!-- NIP Pemohon -->
                <div class="sm:col-span-1">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        NIP Pemohon
                    </label>
                    <input type="text" name="nip_pemohon" value="{{ old('nip_pemohon') }}" placeholder="1990xxxx xxxxxxxx" 
                           class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>
            </div>

            <!-- Keperluan / Kegiatan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Keperluan / Kegiatan <span class="text-rose-500">*</span>
                </label>
                <textarea name="keperluan" rows="3" required placeholder="Contoh: Operasional pencetakan kuesioner Survei Ekonomi dan persediaan rapat koordinasi..." 
                          class="w-full bg-white border border-slate-300 rounded-lg p-3 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">{{ old('keperluan') }}</textarea>
            </div>
        </div>

        <!-- Card: Daftar Barang yang Diminta -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-xs space-y-4 mb-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#094886]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span>Daftar Barang Persediaan yang Diminta</span>
                </h2>
                <button type="button" @click="addItem()" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-[#094886] text-xs font-bold rounded-lg border border-blue-200 transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Tambah Baris Barang</span>
                </button>
            </div>

            <!-- Table of Items -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase">
                        <tr>
                            <th class="px-3 py-3 w-10 text-center">NO</th>
                            <th class="px-3 py-3">PILIH BARANG PERSEDIAAN</th>
                            <th class="px-3 py-3 text-center w-28">SISA STOK</th>
                            <th class="px-3 py-3 text-center w-28">JUMLAH <span class="text-rose-500">*</span></th>
                            <th class="px-3 py-3">CATATAN / SPESIFIKASI</th>
                            <th class="px-3 py-3 w-12 text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(item, idx) in items" :key="idx">
                            <tr class="align-top">
                                <td class="px-3 py-3 text-center text-xs font-medium text-slate-400 pt-5" x-text="idx + 1"></td>
                                
                                <!-- Pilih Barang -->
                                <td class="px-3 py-3">
                                    <select :name="'items[' + idx + '][supply_id]'" 
                                            x-model="item.supply_id" 
                                            @change="onSupplyChange(item)" 
                                            required 
                                            class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer">
                                        <option value="">-- Pilih Barang Persediaan --</option>
                                        <template x-for="s in suppliesList" :key="s.id">
                                            <option :value="s.id" x-text="'[' + s.kode_barang + '] ' + s.nama_barang + ' (' + s.satuan + ')'"></option>
                                        </template>
                                    </select>
                                    <template x-if="item.selectedSupply && item.selectedSupply.spesifikasi">
                                        <div class="text-[11px] text-slate-400 mt-1 italic" x-text="item.selectedSupply.spesifikasi"></div>
                                    </template>
                                </td>

                                <!-- Sisa Stok -->
                                <td class="px-3 py-3 text-center pt-5">
                                    <template x-if="item.selectedSupply">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold"
                                              :class="item.selectedSupply.stok <= 0 ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700'"
                                              x-text="item.selectedSupply.stok + ' ' + item.selectedSupply.satuan"></span>
                                    </template>
                                    <template x-if="!item.selectedSupply">
                                        <span class="text-xs text-slate-400">-</span>
                                    </template>
                                </td>

                                <!-- Jumlah Diminta -->
                                <td class="px-3 py-3">
                                    <div class="relative">
                                        <input type="number" 
                                               :name="'items[' + idx + '][jumlah]'" 
                                               x-model.number="item.jumlah" 
                                               min="1" 
                                               required 
                                               placeholder="1"
                                               class="w-full bg-white border rounded-lg px-3 py-2 text-sm text-slate-800 text-center font-bold focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                               :class="(item.selectedSupply && item.jumlah > item.selectedSupply.stok) ? 'border-rose-400 bg-rose-50 text-rose-700' : 'border-slate-300 focus:border-blue-500'">
                                    </div>
                                    <template x-if="item.selectedSupply && item.jumlah > item.selectedSupply.stok">
                                        <div class="text-[10px] text-rose-600 font-bold mt-1 text-center">
                                            Melebihi stok!
                                        </div>
                                    </template>
                                </td>

                                <!-- Catatan Item -->
                                <td class="px-3 py-3">
                                    <input type="text" 
                                           :name="'items[' + idx + '][catatan]'" 
                                           x-model="item.catatan" 
                                           placeholder="Keterangan opsional..." 
                                           class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                </td>

                                <!-- Tombol Hapus Baris -->
                                <td class="px-3 py-3 text-center pt-4">
                                    <button type="button" @click="removeItem(idx)" 
                                            x-show="items.length > 1"
                                            class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                            title="Hapus baris">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Petunjuk Alur -->
            <div class="p-3.5 bg-blue-50/70 border border-blue-100 rounded-xl text-xs text-blue-900 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-[#094886] flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                <div class="leading-relaxed">
                    <strong>Alur Distribusi Persediaan:</strong> Setelah permohonan ini disimpan, petugas pengelola persediaan dapat menyetujui dan mendistribusikan barang. Pada saat distribusi, <strong>stok barang akan otomatis berkurang</strong> dan sistem langsung <strong>meng-generate Berita Acara Serah Terima (BAST)</strong> resmi yang siap dicetak/diekspor ke PDF.
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Simpan -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('supply.permintaan.index') }}" 
               class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                Batal
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 bg-[#094886] hover:bg-[#073868] text-white text-sm font-bold rounded-lg shadow-sm transition-all cursor-pointer">
                Simpan Permintaan Barang
            </button>
        </div>

    </form>

</div>

<script>
function supplyPermintaanForm(supplies) {
    return {
        suppliesList: supplies,
        items: [
            { supply_id: '', selectedSupply: null, jumlah: 1, catatan: '' }
        ],

        addItem() {
            this.items.push({
                supply_id: '',
                selectedSupply: null,
                jumlah: 1,
                catatan: ''
            });
        },

        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },

        onSupplyChange(item) {
            item.selectedSupply = this.suppliesList.find(s => s.id == item.supply_id) || null;
        },

        validateBeforeSubmit(e) {
            let hasError = false;
            let msg = '';

            for (let i = 0; i < this.items.length; i++) {
                const it = this.items[i];
                if (!it.supply_id) {
                    hasError = true;
                    msg = 'Semua baris barang harus dipilih.';
                    break;
                }
                if (!it.jumlah || it.jumlah <= 0) {
                    hasError = true;
                    msg = 'Jumlah permintaan barang minimal 1.';
                    break;
                }
            }

            if (hasError) {
                e.preventDefault();
                alert(msg);
            }
        }
    };
}
</script>
@endsection
