<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FinanceEntryController;
use App\Http\Controllers\WarungController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [WarungController::class, 'dashboard'])->name('dashboard');
    Route::get('/laporan', [WarungController::class, 'laporan'])->name('laporan');

    Route::middleware('role:owner')->group(function () {
        Route::post('/keuangan', [FinanceEntryController::class, 'store'])->name('finance-entries.store');
        Route::put('/keuangan/{financeEntry}', [FinanceEntryController::class, 'update'])->name('finance-entries.update');
        Route::delete('/keuangan/{financeEntry}', [FinanceEntryController::class, 'destroy'])->name('finance-entries.destroy');
    });

    Route::middleware('role:cashier')->group(function () {
        Route::get('/stok', [WarungController::class, 'stok'])->name('stok');
        Route::post('/stok/barang', [WarungController::class, 'storeBarang'])->name('barang.store');
        Route::put('/stok/barang/{barang}', [WarungController::class, 'updateBarang'])->name('barang.update');
        Route::delete('/stok/barang/{barang}', [WarungController::class, 'destroyBarang'])->name('barang.destroy');

        Route::get('/penjualan', [WarungController::class, 'penjualan'])->name('penjualan');
        Route::post('/penjualan', [WarungController::class, 'storePenjualan'])->name('penjualan.store');

        Route::get('/pembelian', [WarungController::class, 'pembelian'])->name('pembelian');
        Route::post('/pembelian', [WarungController::class, 'storePembelian'])->name('pembelian.store');
        Route::post('/supplier', [WarungController::class, 'storeSupplier'])->name('supplier.store');
    });
});
