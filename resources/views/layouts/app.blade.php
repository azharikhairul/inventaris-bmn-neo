<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SIPANTAU' }} - sistem informasi pemeliharaan dan aset terpadu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Alpine.js for lightweight UI reactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <!-- Synchronous sidebar state check to prevent layout shifts / rubber-banding on page load -->
    <script>
        (function() {
            try {
                var isDesktop = window.innerWidth >= 1024;
                if (isDesktop) {
                    var state = localStorage.getItem('sidebar_state');
                    if (state === 'closed') {
                        document.documentElement.classList.add('sidebar-closed');
                    } else {
                        document.documentElement.classList.add('sidebar-open');
                    }
                } else {
                    // Mobile is closed by default
                    document.documentElement.classList.add('sidebar-closed');
                }
            } catch(e) {}
        })();
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        }
        [x-cloak] { display: none !important; }

        /* Mencegah animasi ketarik-tarik saat halaman refresh atau berpindah halaman */
        .preload, .preload * {
            -webkit-transition: none !important;
            -moz-transition: none !important;
            -ms-transition: none !important;
            -o-transition: none !important;
            transition: none !important;
        }

        /* Initial layout sebelum Alpine.js aktif */
        @media (min-width: 1024px) {
            html.sidebar-closed aside#app-sidebar {
                width: 5rem !important; /* 80px */
                transform: translateX(0) !important;
            }
            html.sidebar-closed #app-main-content {
                margin-left: 5rem !important; /* 80px */
            }
            html.sidebar-open aside#app-sidebar {
                width: 16rem !important; /* 256px */
                transform: translateX(0) !important;
            }
            html.sidebar-open #app-main-content {
                margin-left: 16rem !important; /* 256px */
            }
        }
        @media (max-width: 1023px) {
            html.sidebar-closed aside#app-sidebar {
                transform: translateX(-100%) !important;
                width: 16rem !important;
            }
            html.sidebar-closed #app-main-content {
                margin-left: 0 !important;
            }
            html.sidebar-open aside#app-sidebar {
                transform: translateX(0) !important;
                width: 16rem !important;
            }
            html.sidebar-open #app-main-content {
                margin-left: 0 !important;
            }
        }

        @media print {
            nav, aside, header, #app-sidebar, #app-header, .no-print, [role="navigation"] {
                display: none !important;
            }
            html, body {
                display: block !important;
                position: static !important;
                background-color: #ffffff !important;
                color: #000000 !important;
                height: auto !important;
                min-height: 0 !important;
                overflow: visible !important;
            }
            #app-main-content {
                display: block !important;
                position: static !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                overflow: visible !important;
                height: auto !important;
            }
        }
    </style>
</head>
<body class="preload bg-[#f4f6fa] text-slate-700 antialiased min-h-screen flex" 
      x-data="{ 
          isDesktop: window.innerWidth >= 1024,
          sidebarOpen: window.innerWidth >= 1024 ? (localStorage.getItem('sidebar_state') !== 'closed') : false,
          toggleSidebar() {
              this.sidebarOpen = !this.sidebarOpen;
              if (window.innerWidth >= 1024) {
                  localStorage.setItem('sidebar_state', this.sidebarOpen ? 'open' : 'closed');
              }
              this.syncClass();
          },
          closeMobileSidebar() {
              if (window.innerWidth < 1024) {
                  this.sidebarOpen = false;
                  this.syncClass();
              }
          },
          syncClass() {
              if (this.sidebarOpen) {
                  document.documentElement.classList.remove('sidebar-closed');
                  document.documentElement.classList.add('sidebar-open');
              } else {
                  document.documentElement.classList.remove('sidebar-open');
                  document.documentElement.classList.add('sidebar-closed');
              }
          },
          handleResize() {
              const nowDesktop = window.innerWidth >= 1024;
              if (nowDesktop !== this.isDesktop) {
                  this.isDesktop = nowDesktop;
                  if (nowDesktop) {
                      this.sidebarOpen = localStorage.getItem('sidebar_state') !== 'closed';
                  } else {
                      this.sidebarOpen = false;
                  }
                  this.syncClass();
              }
          }
      }"
      @resize.window.debounce.150ms="handleResize()">

    <!-- Mobile Backdrop Overlay -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="toggleSidebar()"
         class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-30 lg:hidden" x-cloak>
    </div>

    <!-- Sidebar -->
    <aside id="app-sidebar"
           class="bg-white border-r border-slate-200/80 flex flex-col fixed inset-y-0 left-0 z-40 select-none transition-[width,transform] duration-300 ease-in-out shadow-sm lg:shadow-none"
           :class="sidebarOpen ? 'w-64 translate-x-0' : 'w-20 -translate-x-full lg:translate-x-0'">
        <!-- Logo Header -->
        <div class="h-20 flex items-center border-b border-slate-100 lg:border-none transition-[padding] duration-300"
             :class="sidebarOpen ? 'justify-between px-6' : 'justify-center px-0'">
            <div class="flex items-center overflow-hidden">
                <!-- BPS Logo PNG -->
                <div class="w-17 h-17 flex-shrink-0 flex items-center justify-center">
                    <img src="{{ asset('logo_bps.png') }}" alt="Logo BPS" class="w-17 h-17 object-contain flex-shrink-0">
                </div>
                <div x-show="sidebarOpen" x-transition.opacity.duration.200ms class="truncate">
                    <h1 class="text-[20px] font-extrabold text-[#0c2340] leading-tight tracking-tight">SIPANTAU</h1>
                </div>
            </div>

            <!-- Close Sidebar Button ONLY on Mobile (Hidden on Desktop) -->
            <button type="button" @click="toggleSidebar()" 
                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer lg:hidden"
                    title="Tutup Sidebar">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 py-3 overflow-y-auto overflow-x-hidden transition-all duration-300 space-y-4"
             :class="sidebarOpen ? 'px-3' : 'px-2.5'">

            <!-- ================= 1. MENU UTAMA ================= -->
            <div>
                <div x-show="sidebarOpen" class="px-3.5 pt-1 pb-1.5 text-[11px] font-bold text-slate-400 uppercase tracking-wider select-none">
                    MENU UTAMA
                </div>
                <div x-show="!sidebarOpen" class="w-8 h-px bg-slate-200/80 mx-auto my-2"></div>

                <div class="space-y-1">
                    <!-- Dashboard -->
                    @php $isDashboardActive = request()->routeIs('dashboard', 'home') || request()->is('/'); @endphp
                    <a href="{{ route('dashboard') }}" 
                       class="relative flex items-center rounded-xl text-sm transition-all overflow-hidden {{ $isDashboardActive ? 'bg-[#f0f2f5] text-[#094886] font-semibold' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 font-medium' }}"
                       :class="sidebarOpen ? 'gap-3 px-3.5 py-2.5' : 'justify-center w-11 h-11 mx-auto'"
                       title="Dashboard">
                        @if($isDashboardActive)
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[4.5px] h-6 bg-[#094886] rounded-r-md"></div>
                        @endif
                        <svg class="w-5 h-5 flex-shrink-0 {{ $isDashboardActive ? 'text-[#094886]' : 'text-slate-700' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                            <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                            <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                            <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                        </svg>
                        <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="truncate flex-1">Dashboard</span>
                    </a>

                    @if(!\App\Services\SatkerService::isViewingOtherSatker() && auth()->user() && auth()->user()->isAdmin())
                        {{-- Manajemen Pengguna (Khusus Admin) --}}
                        @php 
                            $isUserActive = request()->routeIs('users.*');
                            $userCountCacheKey = 'sidebar_user_count_' . (\App\Services\SatkerService::getActiveSatkerId() ?? 'all');
                            $userCount = \Illuminate\Support\Facades\Cache::remember($userCountCacheKey, 30, function () {
                                return \App\Services\SatkerService::filterQuery(\App\Models\User::query())->count();
                            });
                        @endphp
                        <a href="{{ route('users.index') }}" 
                           class="relative flex items-center rounded-xl text-sm transition-all overflow-hidden {{ $isUserActive ? 'bg-[#f0f2f5] text-[#094886] font-semibold' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 font-medium' }}"
                           :class="sidebarOpen ? 'gap-3 px-3.5 py-2.5' : 'justify-center w-11 h-11 mx-auto'"
                           title="Manajemen Pengguna">
                            @if($isUserActive)
                                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[4.5px] h-6 bg-[#094886] rounded-r-md"></div>
                            @endif
                            <svg class="w-5 h-5 flex-shrink-0 {{ $isUserActive ? 'text-[#094886]' : 'text-slate-700' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="truncate flex-1">Manajemen Pengguna</span>
                            <span x-show="sidebarOpen" class="min-w-[20px] h-5 px-1.5 text-[11px] font-semibold text-slate-700 bg-slate-200/90 rounded-full flex items-center justify-center ml-auto">
                                {{ $userCount }}
                            </span>
                        </a>
                    @endif

                    @if(!\App\Services\SatkerService::isViewingOtherSatker() && auth()->check() && auth()->user()->canAccessAllSatkers() && \App\Services\SatkerService::isMultiSatkerEnabled())
                        {{-- Manajemen Satker (Admin Provinsi Only) --}}
                        @php 
                            $isSatkerActive = request()->routeIs('satker.*'); 
                            $satkerCount = \App\Models\Satker::count();
                        @endphp
                        <a href="{{ route('satker.index') }}" 
                           class="relative flex items-center rounded-xl text-sm transition-all overflow-hidden {{ $isSatkerActive ? 'bg-[#f0f2f5] text-[#094886] font-semibold' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 font-medium' }}"
                           :class="sidebarOpen ? 'gap-3 px-3.5 py-2.5' : 'justify-center w-11 h-11 mx-auto'"
                           title="Manajemen Satker">
                            @if($isSatkerActive)
                                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[4.5px] h-6 bg-[#094886] rounded-r-md"></div>
                            @endif
                            <svg class="w-5 h-5 flex-shrink-0 {{ $isSatkerActive ? 'text-[#094886]' : 'text-slate-700' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                                <line x1="9" y1="22" x2="9" y2="22.01"></line>
                                <line x1="15" y1="22" x2="15" y2="22.01"></line>
                                <line x1="12" y1="22" x2="12" y2="22.01"></line>
                                <line x1="8" y1="6" x2="8" y2="6.01"></line>
                                <line x1="16" y1="6" x2="16" y2="6.01"></line>
                                <line x1="8" y1="10" x2="8" y2="10.01"></line>
                                <line x1="16" y1="10" x2="16" y2="10.01"></line>
                                <line x1="8" y1="14" x2="8" y2="14.01"></line>
                                <line x1="16" y1="14" x2="16" y2="14.01"></line>
                            </svg>
                            <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="truncate flex-1">Manajemen Satker</span>
                            <span x-show="sidebarOpen" class="min-w-[20px] h-5 px-1.5 text-[11px] font-semibold text-slate-700 bg-slate-200/90 rounded-full flex items-center justify-center ml-auto">
                                {{ $satkerCount }}
                            </span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- ================= 2. PERMINTAAN & PEMELIHARAAN ================= -->
            <div>
                <div x-show="sidebarOpen" class="px-3.5 pt-2 pb-1.5 text-[11px] font-bold text-slate-400 uppercase tracking-wider select-none">
                    PERMINTAAN & PEMELIHARAAN
                </div>
                <div x-show="!sidebarOpen" class="w-8 h-px bg-slate-200/80 mx-auto my-2"></div>

                <div class="space-y-1">
                    <!-- Data Aset -->
                    @php $isAsetActive = request()->routeIs('aset.*'); @endphp
                    <a href="{{ route('aset.index') }}" 
                       class="relative flex items-center rounded-xl text-sm transition-all overflow-hidden {{ $isAsetActive ? 'bg-[#f0f2f5] text-[#094886] font-semibold' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 font-medium' }}"
                       :class="sidebarOpen ? 'gap-3 px-3.5 py-2.5' : 'justify-center w-11 h-11 mx-auto'"
                       title="Data Aset">
                        @if($isAsetActive)
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[4.5px] h-6 bg-[#094886] rounded-r-md"></div>
                        @endif
                        <svg class="w-5 h-5 flex-shrink-0 {{ $isAsetActive ? 'text-[#094886]' : 'text-slate-700' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                            <polyline points="2 17 12 22 22 17"></polyline>
                            <polyline points="2 12 12 17 22 12"></polyline>
                        </svg>
                        <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="truncate flex-1">Data Aset</span>
                    </a>

                    <!-- Menu Permintaan -->
                    @php 
                        $isPermintaanActive = request()->routeIs('permintaan.*') && !request()->routeIs('supply.permintaan.*');
                    @endphp
                    <a href="{{ route('permintaan.index') }}" 
                       class="relative flex items-center rounded-xl text-sm transition-all overflow-hidden {{ $isPermintaanActive ? 'bg-[#f0f2f5] text-[#094886] font-semibold' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 font-medium' }}"
                       :class="sidebarOpen ? 'gap-3 px-3.5 py-2.5' : 'justify-center w-11 h-11 mx-auto'"
                       title="Permintaan">
                        @if($isPermintaanActive)
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[4.5px] h-6 bg-[#094886] rounded-r-md"></div>
                        @endif
                        <svg class="w-5 h-5 flex-shrink-0 {{ $isPermintaanActive ? 'text-[#094886]' : 'text-slate-700' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                            <path d="M9 12h6"></path>
                            <path d="M9 16h6"></path>
                        </svg>
                        <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="truncate flex-1">Permintaan</span>
                    </a>

                    <!-- Realisasi -->
                    @php $isRealisasiActive = request()->routeIs('realisasi.*'); @endphp
                    <a href="{{ route('realisasi.index') }}" 
                       class="relative flex items-center rounded-xl text-sm transition-all overflow-hidden {{ $isRealisasiActive ? 'bg-[#f0f2f5] text-[#094886] font-semibold' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 font-medium' }}"
                       :class="sidebarOpen ? 'gap-3 px-3.5 py-2.5' : 'justify-center w-11 h-11 mx-auto'"
                       title="Realisasi Pekerjaan">
                        @if($isRealisasiActive)
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[4.5px] h-6 bg-[#094886] rounded-r-md"></div>
                        @endif
                        <svg class="w-5 h-5 flex-shrink-0 {{ $isRealisasiActive ? 'text-[#094886]' : 'text-slate-700' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                            <line x1="2" y1="10" x2="22" y2="10"></line>
                        </svg>
                        <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="truncate flex-1">Realisasi</span>
                    </a>

                    <!-- Kartu Kendali -->
                    @php $isKartuKendaliActive = request()->routeIs('kartu-kendali.*'); @endphp
                    <a href="{{ route('kartu-kendali.index') }}" 
                       class="relative flex items-center rounded-xl text-sm transition-all overflow-hidden {{ $isKartuKendaliActive ? 'bg-[#f0f2f5] text-[#094886] font-semibold' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 font-medium' }}"
                       :class="sidebarOpen ? 'gap-3 px-3.5 py-2.5' : 'justify-center w-11 h-11 mx-auto'"
                       title="Kartu Kendali">
                        @if($isKartuKendaliActive)
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[4.5px] h-6 bg-[#094886] rounded-r-md"></div>
                        @endif
                        <svg class="w-5 h-5 flex-shrink-0 {{ $isKartuKendaliActive ? 'text-[#094886]' : 'text-slate-700' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                        </svg>
                        <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="truncate flex-1">Kartu Kendali</span>
                    </a>
                </div>
            </div>

            <!-- ================= 3. PERSEDIAAN & GUDANG ================= -->
            <div>
                <div x-show="sidebarOpen" class="px-3.5 pt-2 pb-1.5 text-[11px] font-bold text-slate-400 uppercase tracking-wider select-none">
                    PERSEDIAAN & GUDANG
                </div>
                <div x-show="!sidebarOpen" class="w-8 h-px bg-slate-200/80 mx-auto my-2"></div>

                <div class="space-y-1">
                    <!-- Stok Persediaan -->
                    @php $isInventoryActive = request()->routeIs('supply.inventory.*'); @endphp
                    <a href="{{ route('supply.inventory.index') }}" 
                       class="relative flex items-center rounded-xl text-sm transition-all overflow-hidden {{ $isInventoryActive ? 'bg-[#f0f2f5] text-[#094886] font-semibold' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 font-medium' }}"
                       :class="sidebarOpen ? 'gap-3 px-3.5 py-2.5' : 'justify-center w-11 h-11 mx-auto'"
                       title="Stok Persediaan">
                        @if($isInventoryActive)
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[4.5px] h-6 bg-[#094886] rounded-r-md"></div>
                        @endif
                        <svg class="w-5 h-5 flex-shrink-0 {{ $isInventoryActive ? 'text-[#094886]' : 'text-slate-700' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                        <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="truncate flex-1">Stok Persediaan</span>
                    </a>

                    <!-- Distribusi Barang -->
                    @php $isDistribusiActive = request()->routeIs('supply.distribusi.*'); @endphp
                    <a href="{{ route('supply.distribusi.index') }}" 
                       class="relative flex items-center rounded-xl text-sm transition-all overflow-hidden {{ $isDistribusiActive ? 'bg-[#f0f2f5] text-[#094886] font-semibold' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 font-medium' }}"
                       :class="sidebarOpen ? 'gap-3 px-3.5 py-2.5' : 'justify-center w-11 h-11 mx-auto'"
                       title="Distribusi Barang">
                        @if($isDistribusiActive)
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[4.5px] h-6 bg-[#094886] rounded-r-md"></div>
                        @endif
                        <svg class="w-5 h-5 flex-shrink-0 {{ $isDistribusiActive ? 'text-[#094886]' : 'text-slate-700' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="3" width="15" height="13"></rect>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                            <circle cx="5.5" cy="18.5" r="2.5"></circle>
                            <circle cx="18.5" cy="18.5" r="2.5"></circle>
                        </svg>
                        <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="truncate flex-1">Distribusi Barang</span>
                    </a>
                </div>
            </div>

            @if(!\App\Services\SatkerService::isViewingOtherSatker())
            {{-- ================= 4. SISTEM ================= --}}
            <div>
                <div x-show="sidebarOpen" class="px-3.5 pt-2 pb-1.5 text-[11px] font-bold text-slate-400 uppercase tracking-wider select-none">
                    SISTEM
                </div>
                <div x-show="!sidebarOpen" class="w-8 h-px bg-slate-200/80 mx-auto my-2"></div>

                <div class="space-y-1">
                    {{-- Profil Saya --}}
                    @php $isProfileActive = request()->routeIs('profile.*'); @endphp
                    <a href="{{ route('profile.index') }}" 
                       class="relative flex items-center rounded-xl text-sm transition-all overflow-hidden {{ $isProfileActive ? 'bg-[#f0f2f5] text-[#094886] font-semibold' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 font-medium' }}"
                       :class="sidebarOpen ? 'gap-3 px-3.5 py-2.5' : 'justify-center w-11 h-11 mx-auto'"
                       title="Profil Saya">
                        @if($isProfileActive)
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[4.5px] h-6 bg-[#094886] rounded-r-md"></div>
                        @endif
                        <svg class="w-5 h-5 flex-shrink-0 {{ $isProfileActive ? 'text-[#094886]' : 'text-slate-700' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="truncate flex-1">Profil Saya</span>
                    </a>
                </div>
            </div>
            @endif

        </nav>

        <!-- Bottom Menu -->
        <div class="py-3 border-t border-slate-200/80 space-y-1.5 transition-all duration-300"
             :class="sidebarOpen ? 'px-3' : 'px-2.5'">
            <form action="{{ route('logout') }}" method="POST" class="relative group m-0">
                @csrf
                <button type="submit" 
                        class="w-full flex items-center rounded-xl text-sm font-medium text-rose-600 hover:bg-rose-50 hover:text-rose-700 transition-all cursor-pointer"
                        :class="sidebarOpen ? 'gap-3 px-3.5 py-2.5' : 'justify-center w-11 h-11 mx-auto'"
                        title="Logout">
                    <svg class="w-5 h-5 flex-shrink-0 text-rose-500 group-hover:text-rose-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="truncate font-semibold">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div id="app-main-content" 
         class="flex-1 flex flex-col min-h-screen min-w-0 transition-[margin] duration-300 ease-in-out"
         :class="sidebarOpen ? 'lg:ml-64' : 'lg:ml-20 ml-0'">
        <!-- Top Navbar (Sticky Top & Same White Background as Sidebar) -->
        <header class="sticky top-0 h-16 sm:h-20 bg-white border-b border-slate-200/80 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-30 shadow-2xs">
            <div class="flex items-center gap-2.5 sm:gap-4 min-w-0">
                <!-- Sidebar Toggle Button (Sidebar Icon with Arrow) -->
                <button type="button" @click="toggleSidebar()" 
                        class="p-2 -ml-1 sm:-ml-2 rounded-lg text-slate-700 hover:text-[#094886] hover:bg-slate-100/80 border border-slate-200/80 bg-slate-50/70 transition-all cursor-pointer shadow-2xs group flex-shrink-0"
                        title="Buka / Tutup Sidebar">
                    <svg class="w-5 h-5 text-slate-600 group-hover:text-[#094886] transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="9" y1="3" x2="9" y2="21"></line>
                        <!-- Arrow indicator inside sidebar rect -->
                        <path x-show="!sidebarOpen" d="M14 9l3 3-3 3"></path>
                        <path x-show="sidebarOpen" d="M17 9l-3 3 3 3"></path>
                    </svg>
                </button>

                <h2 class="text-sm sm:text-base md:text-lg lg:text-[22px] font-bold text-[#0c3167] tracking-tight truncate">Sistem Informasi Pemeliharaan dan Aset Terpadu</h2>
            </div>
            <div class="flex items-center gap-1.5 sm:gap-3 flex-shrink-0">
                <!-- Satker Indicator / Switcher -->
                @if(\App\Services\SatkerService::isMultiSatkerEnabled() && auth()->check())
                    @php
                        $user = auth()->user();
                        $activeSatker = \App\Services\SatkerService::getActiveSatker();
                        $allSatkers = \App\Services\SatkerService::getAllSatkers();
                        $canSwitch = $user->canAccessAllSatkers();
                    @endphp

                    @if($canSwitch)
                    <!-- Switcher Dropdown for Super Admin / Admin Provinsi -->
                    <div class="relative" x-data="{ openSatkerDropdown: false }">
                        @php $isViewingOtherSatker = \App\Services\SatkerService::isViewingOtherSatker(); @endphp
                        <button type="button" 
                                @click="openSatkerDropdown = !openSatkerDropdown"
                                @click.outside="openSatkerDropdown = false"
                                class="flex items-center gap-2 px-3 py-1.5 {{ $isViewingOtherSatker ? 'bg-amber-50 hover:bg-amber-100/80 border-amber-300 text-amber-900' : 'bg-[#f0f6fc] hover:bg-[#e4ebf6] border-blue-200/90 text-[#094886]' }} border rounded-xl text-xs font-bold transition-all cursor-pointer shadow-2xs">
                            <svg class="w-4 h-4 {{ $isViewingOtherSatker ? 'text-amber-700' : 'text-[#094886]' }} flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                                <line x1="9" y1="22" x2="9" y2="22.01"></line>
                                <line x1="15" y1="22" x2="15" y2="22.01"></line>
                                <line x1="12" y1="22" x2="12" y2="22.01"></line>
                                <line x1="8" y1="6" x2="8" y2="6.01"></line>
                                <line x1="16" y1="6" x2="16" y2="6.01"></line>
                                <line x1="8" y1="10" x2="8" y2="10.01"></line>
                                <line x1="16" y1="10" x2="16" y2="10.01"></line>
                                <line x1="8" y1="14" x2="8" y2="14.01"></line>
                                <line x1="16" y1="14" x2="16" y2="14.01"></line>
                            </svg>
                            <span class="max-w-[130px] sm:max-w-[180px] md:max-w-[220px] truncate">
                                {{ $activeSatker ? $activeSatker->nama_satker : 'Semua Satker (Provinsi)' }}
                            </span>
                            @if($isViewingOtherSatker)
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-extrabold bg-amber-200/90 text-amber-900 uppercase tracking-tight">Lihat Saja</span>
                            @endif
                            <svg class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200 flex-shrink-0" :class="openSatkerDropdown ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>

                        <div x-show="openSatkerDropdown" 
                             x-cloak 
                             x-transition
                             class="absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-xl border border-slate-200/90 py-2 z-50 overflow-hidden">
                            <div class="px-3.5 py-1.5 border-b border-slate-100 flex items-center justify-between">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pilih Lingkup Satker</span>
                                <span class="text-[10px] font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full">Provinsi & Kabupaten</span>
                            </div>

                            <form action="{{ route('satker.switch') }}" method="POST" class="m-0">
                                @csrf
                                <!-- Option: Semua Satker -->
                                <button type="submit" name="satker_id" value="all" 
                                        class="w-full text-left px-3.5 py-2.5 text-xs font-semibold hover:bg-slate-50 flex items-center justify-between transition-colors cursor-pointer {{ !$activeSatker ? 'bg-blue-50/70 text-[#094886] font-bold' : 'text-slate-700' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-2 h-2 rounded-full {{ !$activeSatker ? 'bg-[#094886]' : 'bg-slate-300' }}"></div>
                                        <span>🌐 Semua Satker (Rekap Gabungan)</span>
                                    </div>
                                    @if(!$activeSatker)
                                        <span class="text-[10px] font-bold text-[#094886]">Aktif</span>
                                    @endif
                                </button>

                                <div class="h-px bg-slate-100 my-1"></div>

                                <!-- List of Satkers -->
                                <div class="max-h-60 overflow-y-auto divide-y divide-slate-50">
                                    @foreach($allSatkers as $s)
                                        @php $isSelected = $activeSatker && $activeSatker->id === $s->id; @endphp
                                        <button type="submit" name="satker_id" value="{{ $s->id }}" 
                                                class="w-full text-left px-3.5 py-2 text-xs hover:bg-slate-50 flex items-center justify-between transition-colors cursor-pointer {{ $isSelected ? 'bg-blue-50/70 text-[#094886] font-bold' : 'text-slate-700' }}">
                                            <div class="flex items-center gap-2.5 truncate pr-2">
                                                <div class="w-2 h-2 rounded-full flex-shrink-0 {{ $isSelected ? 'bg-[#094886]' : 'bg-slate-300' }}"></div>
                                                <div class="truncate">
                                                    <div class="truncate font-semibold">{{ $s->nama_satker }}</div>
                                                    <div class="text-[10px] text-slate-400 font-mono">{{ $s->tingkat }}</div>
                                                </div>
                                            </div>
                                            @if($isSelected)
                                                <span class="text-[10px] font-bold text-[#094886] flex-shrink-0">Aktif</span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </form>
                        </div>
                    </div>
                    @else
                    <!-- Non-switchable Satker Badge for Kabupaten users -->
                    <div class="flex items-center gap-1.5 px-3 py-1.5 bg-[#f0f6fc] border border-blue-200/80 rounded-xl text-xs font-bold text-[#094886] shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-[#094886] flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                            <line x1="9" y1="22" x2="9" y2="22.01"></line>
                            <line x1="15" y1="22" x2="15" y2="22.01"></line>
                        </svg>
                        <span class="max-w-[160px] truncate">
                            {{ $user->satker ? $user->satker->nama_satker : 'Satker BPS' }}
                        </span>
                    </div>
                    @endif
                @endif

                <!-- User Profile Info & Avatar -->
                @auth
                @if(\App\Services\SatkerService::isViewingOtherSatker())
                <div class="flex items-center gap-3 pl-2 sm:pl-3 border-l border-slate-200/80 select-none">
                    <div class="hidden sm:block text-right">
                        <div class="text-xs font-bold text-slate-800 leading-tight truncate max-w-[150px]">
                            {{ auth()->user()->name ?? auth()->user()->username }}
                        </div>
                        <div class="text-[10px] font-semibold text-[#094886] uppercase tracking-wider">
                            {{ auth()->user()->role ?? 'Admin' }}
                        </div>
                    </div>
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-gradient-to-br from-[#0c2e5b] to-[#094886] text-white flex items-center justify-center font-bold text-xs shadow-2xs ring-2 ring-blue-100 flex-shrink-0" title="{{ auth()->user()->name ?? auth()->user()->username }}">
                        {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->username ?? 'U', 0, 1)) }}
                    </div>
                </div>
                @else
                <a href="{{ route('profile.index') }}" 
                   class="flex items-center gap-3 pl-2 sm:pl-3 border-l border-slate-200/80 hover:opacity-85 transition-opacity group cursor-pointer"
                   title="Buka Profil Saya">
                    <div class="hidden sm:block text-right">
                        <div class="text-xs font-bold text-slate-800 group-hover:text-[#094886] transition-colors leading-tight truncate max-w-[150px]">
                            {{ auth()->user()->name ?? auth()->user()->username }}
                        </div>
                        <div class="text-[10px] font-semibold text-[#094886] uppercase tracking-wider">
                            {{ auth()->user()->role ?? 'Admin' }}
                        </div>
                    </div>
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-gradient-to-br from-[#0c2e5b] to-[#094886] text-white flex items-center justify-center font-bold text-xs shadow-2xs ring-2 ring-blue-100 group-hover:ring-[#094886]/40 transition-all flex-shrink-0" title="{{ auth()->user()->name ?? auth()->user()->username }}">
                        {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->username ?? 'U', 0, 1)) }}
                    </div>
                </a>
                @endif
                @endauth
            </div>
        </header>

        <!-- Page Main -->
        <main class="flex-1 min-w-0 px-3.5 sm:px-6 lg:px-8 pt-4 sm:pt-6 pb-12">
            @if(\App\Services\SatkerService::isViewingOtherSatker())
                @php $currentOtherSatker = \App\Services\SatkerService::getActiveSatker(); @endphp
                <div class="mb-5 p-4 bg-amber-50/95 border border-amber-200/90 rounded-2xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs text-amber-900 shadow-2xs">
                    <div class="flex items-start sm:items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0 mt-0.5 sm:mt-0 shadow-2xs">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </div>
                        <div>
                            <div class="font-bold text-sm text-amber-950 flex items-center gap-2">
                                <span>Mode Lihat Saja (Read-Only Satker)</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-amber-200/90 text-amber-900">Hanya Lihat</span>
                            </div>
                            <p class="text-amber-800/90 mt-0.5 text-xs">
                                Anda sedang memantau data milik <strong>{{ $currentOtherSatker?->nama_satker ?? 'Satker Terpilih' }}</strong>. Penambahan data pada Permintaan, Aset, Persediaan, Realisasi, maupun Pengguna dinonaktifkan untuk satker ini.
                            </p>
                        </div>
                    </div>
                    <form action="{{ route('satker.switch') }}" method="POST" class="m-0 sm:self-center flex-shrink-0">
                        @csrf
                        <button type="submit" name="satker_id" value="{{ \App\Services\SatkerService::getIndukSatkerId() }}" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-200/90 hover:bg-amber-300 text-amber-950 text-xs font-bold rounded-xl transition-all shadow-2xs cursor-pointer">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 14 4 9 9 4"></polyline><path d="M20 20v-7a4 4 0 0 0-4-4H4"></path></svg>
                            <span>Kembali ke Satker Induk</span>
                        </button>
                    </form>
                </div>
            @endif
            @yield('content')
        </main>
    </div>

    <!-- Global Floating Toast Notification Container (Top-Right on Desktop, Full-Width Padded on Mobile) -->
    <div x-data="toastManager()" 
         x-cloak
         @toast.window="addToast($event.detail)"
         class="fixed top-4 sm:top-6 right-4 sm:right-6 left-4 sm:left-auto z-[99999] flex flex-col gap-3 max-w-sm sm:w-full pointer-events-none">
        <template x-for="t in toasts" :key="t.id">
            <div class="pointer-events-auto bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-200/90 p-4 relative overflow-hidden transition-all duration-300"
                 x-transition:enter="transform ease-out duration-300 transition"
                 x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-6 scale-95"
                 x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 translate-x-4 scale-95">
                
                <div class="flex items-start gap-3.5">
                    <!-- Icon according to type -->
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 shadow-2xs"
                         :class="{
                             'bg-emerald-50 text-emerald-600 border border-emerald-100': t.type === 'success',
                             'bg-rose-50 text-rose-600 border border-rose-100': t.type === 'error',
                             'bg-amber-50 text-amber-600 border border-amber-100': t.type === 'warning',
                             'bg-blue-50 text-[#094886] border border-blue-100': t.type === 'info'
                         }">
                        <!-- Success Icon -->
                        <template x-if="t.type === 'success'">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </template>
                        <!-- Error Icon -->
                        <template x-if="t.type === 'error'">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                        </template>
                        <!-- Warning Icon -->
                        <template x-if="t.type === 'warning'">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                <line x1="12" y1="9" x2="12" y2="13"></line>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                        </template>
                        <!-- Info Icon -->
                        <template x-if="t.type === 'info'">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg>
                        </template>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0 pt-0.5">
                        <h4 class="text-sm font-bold text-slate-900 leading-tight" x-text="t.title"></h4>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed" x-text="t.message"></p>
                    </div>

                    <!-- Close Button -->
                    <button type="button" @click="removeToast(t.id)" 
                            class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <!-- Animated Progress Bar -->
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-slate-100 overflow-hidden">
                    <div class="h-full transition-all ease-linear"
                         :class="{
                             'bg-emerald-500': t.type === 'success',
                             'bg-rose-500': t.type === 'error',
                             'bg-amber-500': t.type === 'warning',
                             'bg-[#094886]': t.type === 'info'
                         }"
                         :style="'width: ' + t.progress + '%; transition-duration: 50ms;'"></div>
                </div>
            </div>
        </template>
    </div>

    <!-- Additional Modals/Scripts Stack -->
    @stack('modals')
    @stack('scripts')

    <script>
        function toastManager() {
            return {
                toasts: [],
                init() {
                    let hasServerToast = false;
                    @if(session('success'))
                        hasServerToast = true;
                        const sMsg = @json(session('success'));
                        let sTitle = 'Berhasil!';
                        const lowerMsg = sMsg.toLowerCase();
                        if (lowerMsg.includes('hapus')) sTitle = 'Data Berhasil Dihapus';
                        else if (lowerMsg.includes('ubah') || lowerMsg.includes('perbarui')) sTitle = 'Data Berhasil Diubah';
                        else if (lowerMsg.includes('tambah') || lowerMsg.includes('ditambahkan')) sTitle = 'Data Berhasil Ditambahkan';
                        else if (lowerMsg.includes('selesai')) sTitle = 'Realisasi Berhasil Diselesaikan';
                        else if (lowerMsg.includes('simpan')) sTitle = 'Data Berhasil Disimpan';
                        else if (lowerMsg.includes('proses')) sTitle = 'Data Berhasil Diproses';

                        this.addToast({
                            type: 'success',
                            title: sTitle,
                            message: sMsg
                        });
                    @endif
                    @if(session('error'))
                        hasServerToast = true;
                        this.addToast({
                            type: 'error',
                            title: 'Gagal!',
                            message: @json(session('error'))
                        });
                    @endif
                    @if(session('info'))
                        hasServerToast = true;
                        this.addToast({
                            type: 'info',
                            title: 'Informasi',
                            message: @json(session('info'))
                        });
                    @endif

                    // Dukungan notifikasi pending setelah redirect client-side (fetch)
                    const pendingToast = sessionStorage.getItem('pendingToast');
                    if (pendingToast) {
                        try {
                            const parsed = JSON.parse(pendingToast);
                            if (!hasServerToast) {
                                this.addToast(parsed);
                            }
                        } catch(e) {}
                        sessionStorage.removeItem('pendingToast');
                    }
                },
                addToast(detail) {
                    const id = Date.now() + Math.random();
                    let computedTitle = detail.title;
                    if (!computedTitle) {
                        if (detail.type === 'success') {
                            const m = (detail.message || '').toLowerCase();
                            if (m.includes('hapus')) computedTitle = 'Data Berhasil Dihapus';
                            else if (m.includes('ubah') || m.includes('perbarui')) computedTitle = 'Data Berhasil Diubah';
                            else if (m.includes('tambah') || m.includes('ditambahkan')) computedTitle = 'Data Berhasil Ditambahkan';
                            else if (m.includes('selesai')) computedTitle = 'Realisasi Berhasil Diselesaikan';
                            else if (m.includes('simpan')) computedTitle = 'Data Berhasil Disimpan';
                            else if (m.includes('proses')) computedTitle = 'Data Berhasil Diproses';
                            else computedTitle = 'Berhasil!';
                        } else if (detail.type === 'error') {
                            computedTitle = 'Terjadi Kesalahan';
                        } else if (detail.type === 'warning') {
                            computedTitle = 'Perhatian';
                        } else {
                            computedTitle = 'Pemberitahuan';
                        }
                    }

                    const toast = {
                        id: id,
                        type: detail.type || 'info',
                        title: computedTitle,
                        message: detail.message || '',
                        progress: 100
                    };
                    this.toasts.push(toast);

                    const duration = detail.duration || 4500;
                    const intervalTime = 50;
                    const step = (100 / (duration / intervalTime));

                    const timer = setInterval(() => {
                        toast.progress -= step;
                        if (toast.progress <= 0) {
                            clearInterval(timer);
                            this.removeToast(id);
                        }
                    }, intervalTime);
                },
                removeToast(id) {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }
            };
        }

        window.showToast = function(detail) {
            window.dispatchEvent(new CustomEvent('toast', { detail }));
        };

        // Prevent accidental number changes when scrolling over number inputs
        document.addEventListener('wheel', function () {
            if (document.activeElement && document.activeElement.type === 'number') {
                document.activeElement.blur();
            }
        });

        // Remove preload class after initial paint so toggle transitions work smoothly
        window.addEventListener('DOMContentLoaded', function() {
            requestAnimationFrame(function() {
                setTimeout(function() {
                    document.body.classList.remove('preload');
                }, 50);
            });
        });
    </script>
</body>
</html>
