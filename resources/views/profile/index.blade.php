@extends('layouts.app')

@section('title', 'Profil Pengguna - SIPANTAU')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0c2340] tracking-tight">Profil Pengguna</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola data profil dan pengaturan kata sandi akun Anda.</p>
        </div>
    </div>

    <!-- Alert Messages (Error Validation) -->
    @if ($errors->any())
    <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl text-sm flex items-start gap-3 shadow-2xs">
        <svg class="w-5 h-5 text-rose-500 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <div class="space-y-1">
            <div class="font-bold">Gagal memperbarui profil:</div>
            <ul class="list-disc list-inside text-xs sm:text-sm space-y-0.5 text-rose-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <!-- Profile Hero Card -->
    <div class="bg-gradient-to-br from-[#0c2e5b] via-[#094886] to-[#0c3167] rounded-2xl p-6 text-white shadow-md relative overflow-hidden">
        <!-- Background decorative pattern -->
        <div class="absolute -right-10 -bottom-10 w-48 h-48 rounded-full bg-white/5 pointer-events-none"></div>
        <div class="absolute right-24 -top-8 w-32 h-32 rounded-full bg-white/5 pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 relative z-10">
            <!-- Large Avatar -->
            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-white/15 backdrop-blur-md border-2 border-white/30 flex items-center justify-center text-3xl sm:text-4xl font-black text-white shadow-inner flex-shrink-0 ring-4 ring-white/10">
                {{ strtoupper(substr($user->name ?? $user->username ?? 'U', 0, 1)) }}
            </div>

            <!-- User Meta -->
            <div class="text-center sm:text-left flex-1 space-y-2">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight">{{ $user->name ?? $user->username }}</h2>
                    <!-- Role Badge -->
                    @if(strtolower($user->role ?? '') === 'admin')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-400/20 text-amber-200 border border-amber-300/30">
                            Administrator
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-400/20 text-sky-200 border border-sky-300/30">
                            Staff Pegawai
                        </span>
                    @endif

                    <!-- Status Badge -->
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-400/20 text-emerald-200 border border-emerald-300/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        {{ ucfirst($user->status ?? 'Active') }}
                    </span>
                </div>

                <p class="text-xs sm:text-sm text-blue-100 flex flex-wrap items-center justify-center sm:justify-start gap-2 font-mono">
                    <span class="inline-flex items-center gap-1 bg-white/10 px-2 py-0.5 rounded-md text-xs">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        {{ $user->username ? $user->username . '@bps.go.id' : 'Akun Lokal' }}
                    </span>
                    <span class="text-white/40">•</span>
                    <span class="text-blue-200 text-xs font-sans">
                        Terdaftar: {{ $user->created_at ? $user->created_at->translatedFormat('d F Y') : '-' }}
                    </span>
                </p>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <form action="{{ route('profile.update') }}" method="POST" class="space-y-6" x-data="{ showPass: false, showPassConfirm: false }">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Card: Informasi Akun -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6 space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#094886] flex items-center justify-center">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Informasi Akun</h3>
                </div>

                <!-- Nama Lengkap -->
                <div class="space-y-1.5">
                    <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $user->name) }}" 
                           required 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-hidden focus:bg-white focus:border-[#094886] focus:ring-2 focus:ring-[#094886]/10 transition"
                           placeholder="Masukkan nama lengkap">
                    <p class="text-[11px] text-slate-500">Nama pegawai yang akan tampil pada dokumen dan aktivitas sistem.</p>
                </div>

                <!-- Username BPS (Bisa Diedit) -->
                <div class="space-y-1.5">
                    <label for="username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Username BPS <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="username" 
                               name="username" 
                               value="{{ old('username', $user->username) }}" 
                               required 
                               class="w-full pl-4 pr-28 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono text-slate-800 focus:outline-hidden focus:bg-white focus:border-[#094886] focus:ring-2 focus:ring-[#094886]/10 transition"
                               placeholder="username.bps">
                    </div>
                    <p class="text-[11px] text-slate-500">Username BPS Mail Anda. Pastikan sesuai dengan akun SSO BPS agar proses login lancar.</p>
                </div>

                <!-- Peran / Role (Read-only) -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Peran (Hak Akses)
                    </label>
                    <div class="flex items-center gap-2 p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                        <div class="w-6 h-6 rounded-md bg-blue-100/80 text-[#094886] flex items-center justify-center font-bold text-xs">
                            {{ substr($user->role ?? 'S', 0, 1) }}
                        </div>
                        <div class="flex-1">
                            <span class="text-sm font-semibold text-slate-800">{{ $user->role ?? 'Staff' }}</span>
                            <span class="text-xs text-slate-500 block">{{ strtolower($user->role ?? '') === 'admin' ? 'Akses penuh ke seluruh modul sistem & manajemen user' : 'Akses operasional pemeliharaan aset BMN' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Card: Keamanan & Kata Sandi -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6 space-y-4 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-800">Keamanan & Sandi Lokal</h3>
                    </div>

                    <!-- Information notice about SSO and Local Password -->
                    <div class="bg-blue-50/70 border border-blue-100 rounded-xl p-3.5 text-xs text-slate-600 space-y-1">
                        <div class="font-bold text-[#094886] flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg>
                            <span>Tentang Autentikasi Sandi</span>
                        </div>
                        <p class="leading-relaxed">
                            Anda tetap dapat login menggunakan kata sandi <strong>BPS Mail</strong> Anda. Pengaturan sandi lokal di bawah ini bersifat opsional sebagai cadangan login saat server mail BPS kendala.
                        </p>
                    </div>

                    <!-- Kata Sandi Baru -->
                    <div class="space-y-1.5">
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Kata Sandi Lokal Baru
                        </label>
                        <div class="relative">
                            <input :type="showPass ? 'text' : 'password'" 
                                   id="password" 
                                   name="password" 
                                   class="w-full pl-3.5 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-hidden focus:bg-white focus:border-[#094886] focus:ring-2 focus:ring-[#094886]/10 transition"
                                   placeholder="Kosongkan jika tidak diubah">
                            <button type="button" 
                                    @click="showPass = !showPass" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                                <svg x-show="!showPass" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg x-show="showPass" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                        <p class="text-[11px] text-slate-400">Minimal 6 karakter jika ingin mengganti sandi cadangan lokal.</p>
                    </div>

                    <!-- Konfirmasi Kata Sandi Baru -->
                    <div class="space-y-1.5">
                        <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Konfirmasi Kata Sandi Baru
                        </label>
                        <div class="relative">
                            <input :type="showPassConfirm ? 'text' : 'password'" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   class="w-full pl-3.5 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-hidden focus:bg-white focus:border-[#094886] focus:ring-2 focus:ring-[#094886]/10 transition"
                                   placeholder="Ulangi kata sandi baru">
                            <button type="button" 
                                    @click="showPassConfirm = !showPassConfirm" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                                <svg x-show="!showPassConfirm" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg x-show="showPassConfirm" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <span class="text-xs text-slate-500">Pastikan informasi yang Anda masukkan sudah benar sebelum menyimpan.</span>
            <button type="submit" 
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-[#094886] hover:bg-[#0c3167] text-white px-6 py-2.5 rounded-xl font-semibold text-sm shadow-xs hover:shadow transition-all duration-200 cursor-pointer">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                <span>Simpan Perubahan Profil</span>
            </button>
        </div>
    </form>
</div>
@endsection
