<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BahanPanganController;
use App\Http\Controllers\MenuHarianController;
use App\Http\Controllers\GiziController;
use App\Http\Controllers\BiayaController;

Route::get('/', fn() => redirect()->route('login'));

Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout',[LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('bahan-pangan')->name('bahan-pangan.')->group(function () {
        Route::get('/',                      [BahanPanganController::class, 'index'])->name('index');
        Route::get('/create',                [BahanPanganController::class, 'create'])->name('create');
        Route::post('/',                     [BahanPanganController::class, 'store'])->name('store');
        Route::get('/{bahanPangan}',         [BahanPanganController::class, 'show'])->name('show');
        Route::get('/{bahanPangan}/edit',    [BahanPanganController::class, 'edit'])->name('edit');
        Route::put('/{bahanPangan}',         [BahanPanganController::class, 'update'])->name('update');
        Route::delete('/{bahanPangan}',      [BahanPanganController::class, 'destroy'])->name('destroy');
        Route::patch('/{bahanPangan}/status',  [BahanPanganController::class, 'toggleStatus'])->name('toggle-status');
    });

    Route::get('/api/bahan-pangan/search', [BahanPanganController::class, 'apiSearch'])
         ->name('api.bahan-pangan.search');
         
    Route::prefix('menu-harian')->name('menu-harian.')->group(function () {
        Route::get('/',                        [MenuHarianController::class, 'index'])->name('index');
        Route::get('/create',                  [MenuHarianController::class, 'create'])->name('create');
        Route::post('/',                       [MenuHarianController::class, 'store'])->name('store');
        Route::get('/{menuHarian}',            [MenuHarianController::class, 'show'])->name('show');
        Route::get('/{menuHarian}/edit',       [MenuHarianController::class, 'edit'])->name('edit');
        Route::put('/{menuHarian}',            [MenuHarianController::class, 'update'])->name('update');
        Route::delete('/{menuHarian}',         [MenuHarianController::class, 'destroy'])->name('destroy');
        Route::patch('/{menuHarian}/finalize', [MenuHarianController::class, 'finalize'])->name('finalize');
    });

    Route::prefix('gizi')->name('gizi.')->group(function () {
        Route::get('/dashboard', [GiziController::class, 'dashboard'])->name('dashboard');
        Route::get('/api/trend', [GiziController::class, 'apiTrend'])->name('api.trend');
    });

    Route::prefix('biaya')->name('biaya.')->group(function () {
        Route::get('/dashboard',          [BiayaController::class, 'dashboard'])->name('dashboard');
        Route::get('/detail/{menu}',      [BiayaController::class, 'detailMenu'])->name('detail-menu');  // ← pastikan ini ADA
        Route::get('/anggaran/{menu}', [BiayaController::class, 'editAnggaran'])->name('edit-anggaran');
        Route::put('/anggaran/{menu}', [BiayaController::class, 'updateAnggaran'])->name('update-anggaran');

        Route::prefix('harga')->name('harga.')->group(function () {
            Route::get('/',             [BiayaController::class, 'indexHarga'])->name('index');
            Route::get('/tambah',       [BiayaController::class, 'createHarga'])->name('create');
            Route::post('/',            [BiayaController::class, 'storeHarga'])->name('store');
            Route::get('/{harga}/edit', [BiayaController::class, 'editHarga'])->name('edit');
            Route::put('/{harga}',      [BiayaController::class, 'updateHarga'])->name('update');
            Route::delete('/{harga}',   [BiayaController::class, 'destroyHarga'])->name('destroy');
        });
    
        Route::post('/api/estimasi', [BiayaController::class, 'apiEstimasi'])->name('api.estimasi');
    });
});