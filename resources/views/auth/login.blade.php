<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Manajemen Inventaris BMN | BPS</title>
    <link rel="icon" href="{{ asset('logo_bps.png') }}" type="image/png">
    
    <!-- Google Fonts Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js for lightweight reactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        
        body {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            background-color: #f8fafc;
        }

        .login-card {
            box-shadow: 0 20px 40px -15px rgba(12, 35, 64, 0.08), 0 0 0 1px rgba(226, 232, 240, 0.9);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between antialiased selection:bg-[#094886] selection:text-white relative text-slate-700 bg-[#f8fafc]">

    <!-- Header / Top Bar (Logo & Status di Luar Card) -->
    <header class="w-full py-5 px-6 sm:px-12 flex items-center justify-between z-10">
        <div class="flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center border border-slate-200/90 shadow-2xs">
                <img src="{{ asset('logo_bps.png') }}" alt="Logo BPS" class="w-full h-full object-contain">
            </div>
            <div>
                <h1 class="text-[#0c2340] font-extrabold text-base sm:text-lg tracking-tight leading-tight">SIPANTAU</h1>
                <p class="text-slate-500 text-xs font-medium">sistem informasi pemeliharaan dan aset terpadu</p>
            </div>
        </div>
        <div class="hidden sm:flex items-center gap-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200/90 px-3.5 py-1.5 rounded-full shadow-2xs">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>BPS Mail IMAP Integration Ready</span>
        </div>
    </header>

    <!-- Main Container (Card Sama Persis Seperti di Awal) -->
    <main class="w-full max-w-md mx-auto px-4 py-4 z-10 my-auto">
        <div class="bg-white rounded-3xl login-card p-7 sm:p-9 relative overflow-hidden">
            
            <!-- Top Gradient Border -->
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#0c2340] via-[#094886] to-[#0284c7]"></div>

            <!-- Card Header (Sama Seperti di Awal dengan Logo BPS) -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-[#eef5fc] border border-blue-100/90  shadow-xs">
                    <img src="{{ asset('logo_bps.png') }}" alt="Logo BPS" class="w-20 h-20 object-contain">
                </div>
                <h2 class="text-2xl font-black text-[#0c2340] tracking-tight">Masuk ke Sistem</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 font-medium">sistem informasi pemeliharaan dan aset terpadu</p>
            </div>

            <!-- Server Integration Notice Banner (Sama Seperti di Awal) -->
            <div class="mb-5 p-3.5 bg-[#f0f6fc] border border-blue-100/90 rounded-2xl flex items-start gap-3">
                <div class="w-7 h-7 rounded-lg bg-[#094886] text-white flex items-center justify-center flex-shrink-0 mt-0.5 shadow-2xs">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                    </svg>
                </div>
                <div class="text-xs text-slate-600 leading-relaxed">
                    <span class="font-bold text-[#0c2340]">Autentikasi SSO / BPS Mail:</span>
                    Login menggunakan akun resmi <span class="font-mono text-[#094886] font-bold">mail.bps.go.id</span> atau <span class="font-mono text-[#094886] font-bold">mailhost.bps.go.id</span>.
                </div>
            </div>

            <!-- Session Alert: Error -->
            @if(session('error'))
            <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 rounded-2xl flex items-start gap-3 text-rose-800 text-xs sm:text-sm">
                <svg class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <div class="flex-1 font-semibold leading-snug">
                    {{ session('error') }}
                </div>
            </div>
            @endif

            <!-- Session Alert: Success -->
            @if(session('success'))
            <div class="mb-5 p-3.5 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-start gap-3 text-emerald-800 text-xs sm:text-sm">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <div class="flex-1 font-semibold leading-snug">
                    {{ session('success') }}
                </div>
            </div>
            @endif

            <!-- Form -->
            <form action="{{ route('login.post') }}" 
                  method="POST" 
                  x-data="{ showPassword: false, isSubmitting: false }" 
                  @submit="isSubmitting = true" 
                  class="space-y-4">
                @csrf

                <!-- Username Input -->
                <div>
                    <label for="username" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Username BPS
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <input type="text" 
                               name="username" 
                               id="username" 
                               value="{{ old('username') }}" 
                               required 
                               autofocus
                               placeholder="Masukkan username BPS (contoh: azhar)"
                               class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-hidden focus:bg-white focus:border-[#094886] focus:ring-2 focus:ring-[#094886]/15 transition-all @error('username') border-rose-500 ring-rose-200 @enderror">
                    </div>
                    @error('username')
                        <p class="text-rose-600 text-xs font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </div>
                        <input :type="showPassword ? 'text' : 'password'" 
                               type="password"
                               name="password" 
                               id="password" 
                               required 
                               placeholder="Masukkan password akun BPS"
                               class="w-full pl-10 pr-11 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-hidden focus:bg-white focus:border-[#094886] focus:ring-2 focus:ring-[#094886]/15 transition-all @error('password') border-rose-500 ring-rose-200 @enderror">
                        
                        <!-- Toggle Show/Hide Password Eye Button -->
                        <button type="button" 
                                @click="showPassword = !showPassword" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer transition-colors"
                                tabindex="-1"
                                title="Tampilkan / Sembunyikan Password">
                            <!-- Eye Open (shown when password is hidden) -->
                            <svg x-show="!showPassword" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <!-- Eye Off / Crossed (shown when password is visible) -->
                            <svg x-show="showPassword" x-cloak style="display: none;" class="w-5 h-5 text-[#094886]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-rose-600 text-xs font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" 
                            :disabled="isSubmitting" 
                            class="w-full flex items-center justify-center gap-2 py-3.5 px-5 rounded-xl text-white font-bold text-sm bg-[#094886] hover:bg-[#0c3167] active:scale-[0.99] transition-all duration-200 shadow-md hover:shadow-lg cursor-pointer disabled:opacity-75 disabled:cursor-not-allowed">
                        
                        <!-- Spinner (ONLY visible during submit) -->
                        <svg x-show="isSubmitting" x-cloak style="display: none;" class="w-5 h-5 animate-spin text-white flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="2" x2="12" y2="6"></line>
                            <line x1="12" y1="18" x2="12" y2="22"></line>
                            <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
                            <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
                            <line x1="2" y1="12" x2="6" y2="12"></line>
                            <line x1="18" y1="12" x2="22" y2="12"></line>
                            <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
                            <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
                        </svg>

                        <!-- Button Text -->
                        <span x-text="isSubmitting ? 'Memverifikasi Akun...' : 'Masuk ke Sistem'">Masuk ke Sistem</span>

                        <!-- Arrow Icon (ONLY visible when NOT submitting) -->
                        <svg x-show="!isSubmitting" class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </button>
                </div>
            </form>

        </div>
    </main>

</body>
</html>
