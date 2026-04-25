<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BahanPanganController;
use App\Http\Controllers\MenuHarianController;
use App\Http\Controllers\GiziController;
use App\Http\Controllers\BiayaController;
use App\Http\Controllers\AnggaranController;
use App\Http\Controllers\SimulasiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\ImportTkpiController;

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
        Route::get('/detail/{menu}',      [BiayaController::class, 'detailMenu'])->name('detail-menu');

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

    Route::prefix('anggaran')->name('anggaran.')->middleware('auth')->group(function () {
        Route::get('/',              [AnggaranController::class, 'index'])->name('index');
        Route::get('/tambah',        [AnggaranController::class, 'create'])->name('create');
        Route::post('/',             [AnggaranController::class, 'store'])->name('store');
        Route::get('/{anggaran}/edit', [AnggaranController::class, 'edit'])->name('edit');
        Route::put('/{anggaran}',    [AnggaranController::class, 'update'])->name('update');
        Route::delete('/{anggaran}',   [AnggaranController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('simulasi')->name('simulasi.')->middleware('auth')->group(function () {
        Route::get('/',           [SimulasiController::class, 'index'])->name('index');
        Route::post('/kalkulasi', [SimulasiController::class, 'kalkulasi'])->name('kalkulasi');
        Route::post('/simpan',    [SimulasiController::class, 'simpan'])->name('simpan');
    });

    Route::prefix('laporan')->name('laporan.')->middleware('auth')->group(function () {
        Route::get('/',              [LaporanController::class, 'index'])->name('index');
        Route::get('/export-excel', [LaporanController::class, 'exportExcel'])->name('export-excel');
        Route::get('/export-pdf',   [LaporanController::class, 'exportPdf'])->name('export-pdf');
    });

    Route::prefix('import-tkpi')->name('import-tkpi.')->middleware(['auth'])->group(function () {
        Route::get('/',         [ImportTkpiController::class, 'index'])->name('index');
        Route::post('/preview', [ImportTkpiController::class, 'preview'])->name('preview');
        Route::post('/import',  [ImportTkpiController::class, 'import'])->name('import');
    });
});