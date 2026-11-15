@extends('layouts.app')

@section('title', 'Manajemen Satker - SIPANTAU')

@section('content')
<div class="space-y-6" x-data="{ addModalOpen: false, editModalOpen: false, editingSatker: {} }">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/90 shadow-2xs">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-[#094886] uppercase tracking-wider mb-1">
                <span>Multi-Satker</span>
                <span>•</span>
                <span>Satuan Kerja BPS</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Manajemen Satuan Kerja</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola daftar instansi kantor satker BPS Provinsi dan Kabupaten/Kota di wilayah Gorontalo.</p>
        </div>

        @if(auth()->user()->isSuperAdmin())
        <button type="button" 
                @click="addModalOpen = true"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#094886] hover:bg-[#0c3167] text-white text-xs font-bold rounded-xl transition-all shadow-md hover:shadow-lg cursor-pointer self-start sm:self-auto">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Tambah Satker Baru</span>
        </button>
        @endif
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Satker</span>
            <span class="text-2xl font-black text-slate-900 mt-1 block">{{ $satkers->count() }}</span>
            <span class="text-[11px] text-slate-500 mt-0.5 block">Satker BPS se-Gorontalo</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Tingkat Provinsi</span>
            <span class="text-2xl font-black text-[#094886] mt-1 block">{{ $satkers->where('tingkat', 'Provinsi')->count() }}</span>
            <span class="text-[11px] text-slate-500 mt-0.5 block">Kantor BPS Provinsi</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Kabupaten / Kota</span>
            <span class="text-2xl font-black text-emerald-600 mt-1 block">{{ $satkers->where('tingkat', 'Kabupaten/Kota')->count() }}</span>
            <span class="text-[11px] text-slate-500 mt-0.5 block">Kantor BPS Cabang Kab/Kota</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total User Satker</span>
            <span class="text-2xl font-black text-amber-600 mt-1 block">{{ $satkers->sum('users_count') }}</span>
            <span class="text-[11px] text-slate-500 mt-0.5 block">Pengguna terdaftar</span>
        </div>
    </div>

    <!-- Satker Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Daftar Satuan Kerja BPS</h2>
            <span class="text-xs font-semibold text-slate-600 bg-slate-50 border border-slate-200 px-2.5 py-1 rounded-full">
                {{ $satkers->count() }} Satker Aktif
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#edf2f9]/70 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="py-3 px-6 w-12 text-center">NO</th>
                        <th class="py-3 px-6">KODE & NAMA SATKER</th>
                        <th class="py-3 px-6">TINGKAT / WILAYAH</th>
                        <th class="py-3 px-6 text-center">ASET BMN</th>
                        <th class="py-3 px-6 text-center">PERMINTAAN</th>
                        <th class="py-3 px-6 text-center">PENGGUNA</th>
                        <th class="py-3 px-6 text-right pr-6">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @foreach($satkers as $index => $satker)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-4 px-6 text-center font-semibold text-xs text-slate-400">{{ $index + 1 }}</td>
                        <td class="py-4 px-6">
                            <div class="font-bold text-slate-900 flex items-center gap-2">
                                <span>{{ $satker->nama_satker }}</span>
                                @if($satker->isProvinsi())
                                    <span class="text-[10px] font-bold text-blue-700 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-full">Induk</span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-400 font-mono mt-0.5">Kode: {{ $satker->kode_satker }}</div>
                            @if($satker->alamat)
                                <div class="text-[11px] text-slate-500 mt-1 truncate max-w-sm">{{ $satker->alamat }}</div>
                            @endif
                        </td>
                        <td class="py-4 px-6">
                            <span class="inline-block text-xs font-semibold px-2.5 py-0.5 rounded-full border {{ $satker->isProvinsi() ? 'bg-blue-50 text-[#094886] border-blue-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' }}">
                                {{ $satker->tingkat }}
                            </span>
                            <div class="text-xs text-slate-500 mt-1">{{ $satker->wilayah_nama }}</div>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <span class="font-bold text-slate-800">{{ $satker->asets->count() }}</span>
                            <span class="text-[11px] text-slate-400 block">Unit</span>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <span class="font-bold text-slate-800">{{ $satker->permintaans_count }}</span>
                            <span class="text-[11px] text-slate-400 block">Berkas</span>
                        </td>
                        <td class="py-4 px-6 text-center">
                            @if($satker->users_count > 0)
                                <span class="font-bold text-slate-800">{{ $satker->users_count }}</span>
                                <span class="text-[11px] text-slate-400 block">Akun</span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full" title="Satker ini belum memiliki akun terdaftar">
                                    0 Akun
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-right pr-6">
                            <div class="flex items-center justify-end gap-2">
                                @if(auth()->user()->canAccessAllSatkers())
                                    @if($satker->users_count === 0)
                                    <form action="{{ route('satker.switch') }}" method="POST" class="m-0">
                                        @csrf
                                        <input type="hidden" name="satker_id" value="{{ $satker->id }}">
                                        <input type="hidden" name="redirect_to" value="{{ route('users.index') }}">
                                        <button type="submit" 
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 rounded-lg text-xs font-semibold transition-colors cursor-pointer shadow-2xs"
                                                title="Beralih ke satker ini dan daftarkan akun Admin Satker">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                            <span>+ Buat Admin</span>
                                        </button>
                                    </form>
                                    @endif
                                <form action="{{ route('satker.switch') }}" method="POST" class="m-0">
                                    @csrf
                                    <input type="hidden" name="satker_id" value="{{ $satker->id }}">
                                    <button type="submit" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-[#094886] border border-blue-200 rounded-lg text-xs font-semibold transition-colors cursor-pointer"
                                            title="Beralih ke konteks satker ini">
                                        <span>Beralih</span>
                                    </button>
                                </form>
                                @endif

                                @if(auth()->user()->isSuperAdmin())
                                <button type="button" 
                                        @click="editingSatker = {{ json_encode($satker) }}; editModalOpen = true"
                                        class="p-1.5 hover:bg-slate-100 text-slate-600 hover:text-slate-900 rounded-lg transition-colors cursor-pointer"
                                        title="Edit Data Satker">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Satker -->
    <div x-show="addModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-xs" @click="addModalOpen = false"></div>
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
                <h3 class="text-lg font-bold text-slate-900 mb-1">Tambah Satker Baru</h3>
                <p class="text-xs text-slate-500 mb-4">Tambahkan kantor cabang BPS kabupaten/kota baru ke dalam sistem.</p>

                <form action="{{ route('satker.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kode Satker (SAKTI/BMN) *</label>
                        <input type="text" name="kode_satker" required placeholder="Contoh: 054013200636901000KD" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#094886] focus:outline-hidden">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Satker *</label>
                        <input type="text" name="nama_satker" required placeholder="Contoh: BPS Kabupaten Boalemo" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#094886] focus:outline-hidden">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tingkat *</label>
                            <select name="tingkat" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#094886] focus:outline-hidden">
                                <option value="Kabupaten/Kota">Kabupaten/Kota</option>
                                <option value="Provinsi">Provinsi</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Wilayah / Kabupaten / Kota</label>
                            <input type="text" name="kabupaten_kota" placeholder="Contoh: Kab. Boalemo atau Provinsi Gorontalo" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#094886] focus:outline-hidden">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alamat Kantor</label>
                        <textarea name="alamat" rows="2" placeholder="Alamat lengkap kantor cabang" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#094886] focus:outline-hidden"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Telepon</label>
                            <input type="text" name="telepon" placeholder="(0435) ..." class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#094886] focus:outline-hidden">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Satker</label>
                            <input type="email" name="email" placeholder="bps7501@bps.go.id" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#094886] focus:outline-hidden">
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="addModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-[#094886] hover:bg-[#0c3167] rounded-xl transition-colors cursor-pointer shadow-xs">Simpan Satker</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Satker -->
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-xs" @click="editModalOpen = false"></div>
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
                <h3 class="text-lg font-bold text-slate-900 mb-1">Edit Satuan Kerja</h3>
                <p class="text-xs text-slate-500 mb-4">Perbarui informasi kantor satuan kerja.</p>

                <form :action="`/satker/${editingSatker.id}`" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kode Satker (SAKTI/BMN) *</label>
                        <input type="text" name="kode_satker" x-model="editingSatker.kode_satker" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#094886] focus:outline-hidden">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Satker *</label>
                        <input type="text" name="nama_satker" x-model="editingSatker.nama_satker" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#094886] focus:outline-hidden">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tingkat *</label>
                            <select name="tingkat" x-model="editingSatker.tingkat" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#094886] focus:outline-hidden">
                                <option value="Kabupaten/Kota">Kabupaten/Kota</option>
                                <option value="Provinsi">Provinsi</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Wilayah / Kabupaten / Kota</label>
                            <input type="text" name="kabupaten_kota" x-model="editingSatker.kabupaten_kota" placeholder="Contoh: Kab. Boalemo atau Provinsi Gorontalo" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#094886] focus:outline-hidden">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alamat Kantor</label>
                        <textarea name="alamat" x-model="editingSatker.alamat" rows="2" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#094886] focus:outline-hidden"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Telepon</label>
                            <input type="text" name="telepon" x-model="editingSatker.telepon" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#094886] focus:outline-hidden">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Satker</label>
                            <input type="email" name="email" x-model="editingSatker.email" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#094886] focus:outline-hidden">
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="editModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-[#094886] hover:bg-[#0c3167] rounded-xl transition-colors cursor-pointer shadow-xs">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
