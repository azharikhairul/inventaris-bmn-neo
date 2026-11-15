<?php

use App\Http\Controllers\AsetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KartuKendaliController;
use App\Http\Controllers\PermintaanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RealisasiController;
use App\Http\Controllers\SatkerController;
use App\Http\Controllers\SupplyDistribusiController;
use App\Http\Controllers\SupplyInventoryController;
use App\Http\Controllers\SupplyPermintaanController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes (Guest)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

/*
|--------------------------------------------------------------------------
| Protected Application Routes (Requires Auth)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Kartu Kendali
    Route::prefix('kartu-kendali')->name('kartu-kendali.')->group(function () {
        Route::get('/', [KartuKendaliController::class, 'index'])->name('index');
        Route::get('/{id}', [KartuKendaliController::class, 'show'])->name('show');
        Route::get('/{id}/export-excel', [KartuKendaliController::class, 'exportExcel'])->name('export');
        Route::get('/{id}/export-pdf', [KartuKendaliController::class, 'exportPdf'])->name('pdf');
    });

    // Data Aset
    Route::prefix('aset')->name('aset.')->group(function () {
        Route::get('/', [AsetController::class, 'index'])->name('index');
        Route::post('/', [AsetController::class, 'store'])->name('store');
        Route::get('/export', [AsetController::class, 'export'])->name('export');
        Route::post('/import', [AsetController::class, 'import'])->name('import');
        Route::post('/rollback-import', [AsetController::class, 'rollbackImport'])->name('rollback-import');
        Route::get('/{id}', [AsetController::class, 'show'])->name('show');
        Route::put('/{id}', [AsetController::class, 'update'])->name('update');
    });

    // Permintaan
    Route::prefix('permintaan')->name('permintaan.')->group(function () {
        Route::get('/', [PermintaanController::class, 'index'])->name('index');
        Route::get('/tambah', [PermintaanController::class, 'create'])->name('create');
        Route::post('/', [PermintaanController::class, 'store'])->name('store');
        Route::get('/{id}', [PermintaanController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PermintaanController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PermintaanController::class, 'update'])->name('update');
        Route::delete('/{id}', [PermintaanController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/kak', [PermintaanController::class, 'generateKak'])->name('kak');
        Route::match(['get', 'post'], '/{id}/kak/pdf', [PermintaanController::class, 'downloadKakPdf'])->name('kak.pdf');
        Route::get('/{id}/bast', [PermintaanController::class, 'generateBast'])->name('bast');
        Route::match(['get', 'post'], '/{id}/bast/pdf', [PermintaanController::class, 'downloadBastPdf'])->name('bast.pdf');
        Route::post('/{id}/proses', [PermintaanController::class, 'prosesRealisasi'])->name('proses');
    });

    // Realisasi
    Route::prefix('realisasi')->name('realisasi.')->group(function () {
        Route::get('/', [RealisasiController::class, 'index'])->name('index');
        Route::get('/{id}', [RealisasiController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [RealisasiController::class, 'edit'])->name('edit');
        Route::put('/{id}', [RealisasiController::class, 'update'])->name('update');
        Route::get('/{id}/proses', [RealisasiController::class, 'proses'])->name('proses');
        Route::post('/{id}/selesai', [RealisasiController::class, 'selesai'])->name('selesai');
        Route::get('/{id}/bast', [PermintaanController::class, 'generateBastFromRealisasi'])->name('bast');
        Route::match(['get', 'post'], '/{id}/bast/pdf', [PermintaanController::class, 'downloadBastPdfFromRealisasi'])->name('bast.pdf');
    });

    // Modul Supply (Persediaan, Permintaan & BAST Distribusi)
    Route::prefix('supply')->name('supply.')->group(function () {
        // 1. Katalog & Stok Barang Persediaan
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [SupplyInventoryController::class, 'index'])->name('index');
            Route::post('/', [SupplyInventoryController::class, 'store'])->name('store');
            Route::get('/export', [SupplyInventoryController::class, 'export'])->name('export');
            Route::put('/{id}', [SupplyInventoryController::class, 'update'])->name('update');
            Route::delete('/{id}', [SupplyInventoryController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/stok-masuk', [SupplyInventoryController::class, 'stokMasuk'])->name('stok-masuk');
            Route::get('/{id}/mutasi', [SupplyInventoryController::class, 'mutasi'])->name('mutasi');
            Route::get('/api/search', [SupplyInventoryController::class, 'apiSearch'])->name('api.search');
        });

        // 2. Permintaan Barang Supply
        Route::prefix('permintaan')->name('permintaan.')->group(function () {
            Route::get('/', [SupplyPermintaanController::class, 'index'])->name('index');
            Route::get('/tambah', [SupplyPermintaanController::class, 'create'])->name('create');
            Route::post('/', [SupplyPermintaanController::class, 'store'])->name('store');
            Route::get('/{id}', [SupplyPermintaanController::class, 'show'])->name('show');
            Route::delete('/{id}', [SupplyPermintaanController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/distribusikan', [SupplyPermintaanController::class, 'distribusikan'])->name('distribusikan');
        });

        // 3. Distribusi Barang Persediaan & BAST Distribusi
        Route::prefix('distribusi')->name('distribusi.')->group(function () {
            Route::get('/', [SupplyDistribusiController::class, 'index'])->name('index');
            Route::get('/tambah', [SupplyDistribusiController::class, 'create'])->name('create');
            Route::post('/', [SupplyDistribusiController::class, 'store'])->name('store');
            Route::get('/{id}', [SupplyDistribusiController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [SupplyDistribusiController::class, 'edit'])->name('edit');
            Route::put('/{id}', [SupplyDistribusiController::class, 'update'])->name('update');
            Route::get('/{id}/print', [SupplyDistribusiController::class, 'print'])->name('print');
            Route::get('/{id}/pdf', [SupplyDistribusiController::class, 'exportPdf'])->name('pdf');
            Route::post('/{id}/upload-ttd', [SupplyDistribusiController::class, 'uploadTtd'])->name('upload-ttd');
            Route::delete('/{id}/delete-ttd', [SupplyDistribusiController::class, 'deleteTtd'])->name('delete-ttd');
            Route::delete('/{id}', [SupplyDistribusiController::class, 'destroy'])->name('destroy');
        });
    });

    // API Search
    Route::get('/api/asets/search', [PermintaanController::class, 'searchAsets'])->name('asets.search');

    // User Management (Admin Only)
    Route::middleware('admin')->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Profil Pengguna (Staff & Admin)
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
    });

    // Satker Switcher & Management
    Route::post('/satker/switch', [SatkerController::class, 'switchSatker'])->name('satker.switch');
    Route::middleware('admin')->prefix('satker')->name('satker.')->group(function () {
        Route::get('/', [SatkerController::class, 'index'])->name('index');
        Route::post('/', [SatkerController::class, 'store'])->name('store');
        Route::put('/{id}', [SatkerController::class, 'update'])->name('update');
    });
});
