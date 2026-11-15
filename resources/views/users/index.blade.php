@extends('layouts.app')

@section('title', 'Manajemen Pengguna BPS')

@section('content')
<div class="space-y-6" x-data="userManagement()">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0c2340] tracking-tight">Manajemen Pengguna</h1>
            <p class="text-sm text-slate-500 mt-1">
                @if(\App\Services\SatkerService::isMultiSatkerEnabled() && \App\Services\SatkerService::getActiveSatker())
                    Lingkup Satker: <span class="font-semibold text-slate-800">{{ \App\Services\SatkerService::getActiveSatker()->nama_satker }}</span> &bull; Daftarkan dan kelola akun pegawai pada satker ini.
                @else
                    Daftarkan akun pegawai BPS agar dapat masuk ke sistem menggunakan SSO / BPS Mail.
                @endif
            </p>
        </div>
        @if(!\App\Services\SatkerService::isViewingOtherSatker())
        <div>
            <button type="button" 
                    @click="openCreateModal()" 
                    class="inline-flex items-center gap-2 bg-[#094886] hover:bg-[#0c3167] text-white px-4 py-2.5 rounded-xl font-semibold text-sm shadow-xs hover:shadow transition-all duration-200 cursor-pointer">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Tambah Pengguna BPS</span>
            </button>
        </div>
        @endif
    </div>

    <!-- Integration Information Banner -->
    <div class="bg-gradient-to-r from-blue-50/90 to-sky-50/80 border border-blue-100/90 rounded-2xl p-4 sm:p-5 shadow-2xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-[#094886] text-white flex items-center justify-center flex-shrink-0 shadow-xs">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-bold text-[#0c2340]">Kebijakan Akses & Sinkronisasi Sandi Mail BPS</h2>
                <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">
                    Hanya akun yang terdaftar di daftar ini yang diizinkan login. Saat pengguna login pertama kali menggunakan akun Mail BPS (<span class="font-mono text-[#094886]">mail.bps.go.id</span> / <span class="font-mono text-[#094886]">mailhost.bps.go.id</span>), sistem akan otomatis meng-enkripsi dan menyimpan hash sandi ke database lokal agar sistem tetap dapat digunakan saat server mail kendala.
                </p>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs">
        <form action="{{ route('users.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari nama atau username BPS..." 
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-hidden focus:bg-white focus:border-[#094886] transition-all">
            </div>

            <div class="flex items-center gap-2">
                <select name="role" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs sm:text-sm rounded-xl py-2 px-3 focus:outline-hidden focus:border-[#094886] cursor-pointer">
                    <option value="">Semua Role</option>
                    <option value="Admin" {{ request('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                    <option value="Staff" {{ request('role') == 'Staff' ? 'selected' : '' }}>Staff</option>
                </select>

                <select name="status" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs sm:text-sm rounded-xl py-2 px-3 focus:outline-hidden focus:border-[#094886] cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>

                @if(request()->hasAny(['search', 'role', 'status']))
                <a href="{{ route('users.index') }}" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition" title="Reset Filter">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Users Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50/80 border-b border-slate-200/80 text-slate-600 font-semibold uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">Pengguna</th>
                        <th class="py-3.5 px-4">Username BPS</th>
                        @if(\App\Services\SatkerService::isMultiSatkerEnabled())
                        <th class="py-3.5 px-4">Satker</th>
                        @endif
                        <th class="py-3.5 px-4">Role</th>
                        <th class="py-3.5 px-4">Status Akun</th>
                        <th class="py-3.5 px-4">Status Sandi Lokal</th>
                        <th class="py-3.5 px-4 sm:px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <!-- User Name & Avatar -->
                        <td class="py-3.5 px-4 sm:px-6">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#0c2e5b] to-[#094886] text-white flex items-center justify-center font-bold text-xs shadow-2xs flex-shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-900 truncate">{{ $user->name }}</div>
                                    <div class="text-[11px] text-slate-400">ID: #{{ $user->id }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Username -->
                        <td class="py-3.5 px-4">
                            <span class="font-mono text-xs bg-slate-100 text-slate-800 px-2 py-1 rounded-md font-medium">
                                {{ $user->username ?? '-' }}
                            </span>
                        </td>

                        <!-- Satker -->
                        @if(\App\Services\SatkerService::isMultiSatkerEnabled())
                        <td class="py-3.5 px-4">
                            @if($user->satker)
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-700 bg-slate-100 border border-slate-200/80 px-2.5 py-1 rounded-lg" title="{{ $user->satker->nama_satker }} ({{ $user->satker->kode_satker }})">
                                <span class="w-1.5 h-1.5 rounded-full {{ $user->satker->isProvinsi() ? 'bg-indigo-600' : 'bg-blue-600' }}"></span>
                                {{ $user->satker->wilayah_nama }}
                            </span>
                            @else
                            <span class="text-xs text-slate-400 italic">Belum diatur</span>
                            @endif
                        </td>
                        @endif

                        <!-- Role -->
                        <td class="py-3.5 px-4">
                            @if(strtolower($user->role) === 'admin')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-600"></span>
                                Admin
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                Staff
                            </span>
                            @endif
                        </td>

                        <!-- Status -->
                        <td class="py-3.5 px-4">
                            @if(strtolower($user->status ?? 'active') === 'active')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                Aktif
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                Nonaktif
                            </span>
                            @endif
                        </td>

                        <!-- Hash Status -->
                        <td class="py-3.5 px-4">
                            @if(!empty($user->password))
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700 bg-emerald-50/70 border border-emerald-200/60 px-2.5 py-1 rounded-lg" title="Sandi tersimpan di database lokal">
                                <svg class="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                Tersinkron Lokal
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-700 bg-amber-50/70 border border-amber-200/60 px-2.5 py-1 rounded-lg" title="Pengguna akan login via BPS Mail pertama kali untuk menyimpan sandi">
                                <svg class="w-3.5 h-3.5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                </svg>
                                Menunggu Login Mail
                            </span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="py-3.5 px-4 sm:px-6 text-center">
                            @if(!\App\Services\SatkerService::isViewingOtherSatker())
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" 
                                        @click="openEditModal({{ json_encode($user) }})" 
                                        class="p-1.5 text-slate-500 hover:text-[#094886] hover:bg-slate-100 rounded-lg transition" 
                                        title="Edit Pengguna">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </button>

                                @if($user->isSuperAdmin())
                                <span class="p-1.5 text-slate-300 inline-flex items-center" title="Akun Administrator utama dilindungi dan tidak dapat dihapus">
                                    <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                    </svg>
                                </span>
                                @elseif($user->id !== auth()->id())
                                <button type="button" 
                                        @click="confirmDelete({{ $user->id }}, '{{ addslashes($user->name) }}')" 
                                        class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" 
                                        title="Hapus Pengguna">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>
                                @endif
                            </div>
                            @else
                            <span class="text-xs text-slate-400 italic">Lihat Saja</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ \App\Services\SatkerService::isMultiSatkerEnabled() ? 7 : 6 }}" class="py-12 text-center text-slate-400">
                            <div class="max-w-md mx-auto">
                                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#094886] flex items-center justify-center mx-auto mb-3 shadow-2xs">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg>
                                </div>
                                <p class="font-bold text-slate-800 text-sm">
                                    @if(\App\Services\SatkerService::getActiveSatker())
                                        Belum Ada Pengguna di {{ \App\Services\SatkerService::getActiveSatker()->nama_satker }}
                                    @else
                                        Tidak ada data pengguna ditemukan
                                    @endif
                                </p>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                    @if(\App\Services\SatkerService::getActiveSatker())
                                        Satker ini belum memiliki akun yang terdaftar. Administrator BPS Provinsi dapat mendaftarkan akun Admin Satker agar petugas di kantor ini dapat mulai login dan mengoperasikan sistem.
                                    @else
                                        Silakan periksa kata kunci pencarian atau filter yang dipilih.
                                    @endif
                                </p>
                                @if(auth()->user()->canAccessAllSatkers() || auth()->user()->isAdmin())
                                <button type="button" 
                                        @click="openCreateModal()" 
                                        class="mt-4 inline-flex items-center gap-2 bg-[#094886] hover:bg-[#0c3167] text-white px-4 py-2 rounded-xl font-semibold text-xs shadow-xs hover:shadow transition-all cursor-pointer">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                    <span>+ Daftarkan Admin Satker Sekarang</span>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    <!-- Create / Edit User Modal -->
    <div x-show="showModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" 
         x-cloak 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="bg-white rounded-2xl shadow-xl border border-slate-200/80 w-full max-w-lg overflow-hidden transform transition-all"
             @click.away="showModal = false">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="font-bold text-[#0c2340] text-base" x-text="isEdit ? 'Edit Data Pengguna BPS' : 'Tambah Pengguna Baru'"></h3>
                <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <!-- Modal Form -->
            <form :action="isEdit ? `/users/${form.id}` : '{{ route('users.store') }}'" method="POST" class="p-6 space-y-4">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nama Lengkap Pegawai <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" x-model="form.name" required placeholder="Nama Lengkap" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886] focus:bg-white">
                </div>

                <!-- Username BPS -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Username BPS <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input type="text" name="username" x-model="form.username" required placeholder="Username" class="w-full pl-3.5 pr-24 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono focus:outline-hidden focus:border-[#094886] focus:bg-white">

                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">Username akun resmi BPS Mail / SSO tanpa @bps.go.id</p>
                </div>

                @if(\App\Services\SatkerService::isMultiSatkerEnabled())
                <!-- Satuan Kerja -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Satuan Kerja (Satker) <span class="text-rose-500">*</span></label>
                    @if(auth()->user()->canAccessAllSatkers())
                    <select name="satker_id" x-model="form.satker_id" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                        <option value="">-- Pilih Satuan Kerja --</option>
                        @foreach($satkers ?? [] as $s)
                            <option value="{{ $s->id }}">{{ $s->nama_satker }} ({{ $s->kode_satker }})</option>
                        @endforeach
                    </select>
                    @else
                    <input type="hidden" name="satker_id" :value="form.satker_id">
                    <div class="w-full px-3 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-700 font-medium">
                        {{ auth()->user()->satker ? auth()->user()->satker->nama_satker : 'Satker BPS' }}
                    </div>
                    @endif
                    <p class="text-[11px] text-slate-500 mt-1">Pengguna akan dibatasi hanya melihat dan mengelola data pada satker ini.</p>
                </div>
                @endif

                <!-- Role & Status Grid -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Role <span class="text-rose-500">*</span></label>
                        <template x-if="isSuperAdminAccount()">
                            <div>
                                <input type="hidden" name="role" value="Admin">
                                <div class="w-full px-3 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-600 font-medium flex items-center justify-between cursor-not-allowed">
                                    <span>Admin</span>
                                    <svg class="w-3.5 h-3.5 text-amber-500 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                    </svg>
                                </div>
                                <p class="text-[10px] text-amber-600 mt-1">Role Administrator utama terkunci</p>
                            </div>
                        </template>
                        <template x-if="!isSuperAdminAccount()">
                            <select name="role" x-model="form.role" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                                <option value="Staff">Staff</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </template>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Status Akun <span class="text-rose-500">*</span></label>
                        <template x-if="isSuperAdminAccount()">
                            <div>
                                <input type="hidden" name="status" value="active">
                                <div class="w-full px-3 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-600 font-medium flex items-center justify-between cursor-not-allowed">
                                    <span class="flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                    <svg class="w-3.5 h-3.5 text-amber-500 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                    </svg>
                                </div>
                                <p class="text-[10px] text-amber-600 mt-1">Status Administrator selalu aktif</p>
                            </div>
                        </template>
                        <template x-if="!isSuperAdminAccount()">
                            <select name="status" x-model="form.status" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </template>
                    </div>
                </div>

                <!-- Password Lokal (Opsional) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Sandi Lokal 
                        <span class="text-slate-400 font-normal lowercase">(opsional / kosongkan untuk sinkronisasi otomatis via mail BPS)</span>
                    </label>
                    <input type="password" name="password" x-model="form.password" placeholder="Kosongkan jika pengguna login via Mail BPS" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886] focus:bg-white">
                    <p class="text-[11px] text-slate-500 mt-1">Jika dikosongkan, sandi akan otomatis diambil dan di-hash dari Mail BPS pada saat pengguna login pertama kali.</p>
                </div>

                <!-- Modal Footer -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-sm font-bold text-white bg-[#094886] hover:bg-[#0c3167] rounded-xl transition cursor-pointer shadow-xs">
                        <span x-text="isEdit ? 'Simpan Perubahan' : 'Daftarkan Pengguna'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" 
         x-cloak 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="bg-white rounded-2xl shadow-xl border border-slate-200/80 w-full max-w-sm overflow-hidden p-6 text-center"
             @click.away="showDeleteModal = false">
            
            <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
            </div>

            <h3 class="font-bold text-slate-800 text-base">Hapus Pengguna?</h3>
            <p class="text-xs text-slate-500 mt-1">
                Apakah Anda yakin ingin menghapus pengguna <span class="font-bold text-slate-700" x-text="deleteUserName"></span>? Tindakan ini tidak dapat dibatalkan.
            </p>

            <form :action="`/users/${deleteUserId}`" method="POST" class="mt-5 flex items-center justify-center gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDeleteModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition cursor-pointer shadow-xs">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function userManagement() {
    return {
        showModal: false,
        showDeleteModal: false,
        isEdit: false,
        deleteUserId: null,
        deleteUserName: '',
        form: {
            id: null,
            name: '',
            username: '',
            satker_id: '',
            role: 'Staff',
            status: 'active',
            password: '',
        },
        openCreateModal() {
            this.isEdit = false;
            this.form = {
                id: null,
                name: '',
                username: '',
                satker_id: '{{ \App\Services\SatkerService::getActiveSatkerId() ?? auth()->user()->satker_id ?? ($satkers->first()->id ?? "") }}',
                role: 'Staff',
                status: 'active',
                password: '',
            };
            this.showModal = true;
        },
        openEditModal(user) {
            this.isEdit = true;
            this.form = {
                id: user.id,
                name: user.name,
                username: user.username || '',
                satker_id: user.satker_id || '',
                role: user.role || 'Staff',
                status: user.status || 'active',
                password: '',
            };
            this.showModal = true;
        },
        confirmDelete(id, name) {
            this.deleteUserId = id;
            this.deleteUserName = name;
            this.showDeleteModal = true;
        },
        isSuperAdminAccount() {
            if (!this.isEdit) return false;
            const name = (this.form.name || '').toLowerCase().trim();
            const username = (this.form.username || '').toLowerCase().trim();
            return this.form.id === 2 || username === 'admin' || name === 'administrator';
        }
    }
}
</script>
@endsection
