<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root ke login atau rute sesuai peran
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->role === 'kasir') return redirect()->route('pos.index');
        if ($user->role === 'admin') return redirect()->route('products.index');
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware('auth')->group(function () {

    // 1. Fitur Khusus Pemilik (Dashboard, Keuangan, Laporan, Manajemen User, Pengaturan Toko)
    Route::middleware('role:pemilik')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Manajemen Keuangan
        Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
        Route::post('/finance', [FinanceController::class, 'store'])->name('finance.store');
        Route::delete('/finance/{finance}', [FinanceController::class, 'destroy'])->name('finance.destroy');

        // Laporan & Ekspor
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

        // Kelola Pengguna
        Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);

        // Pengaturan & Backup
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('/settings/backup', [SettingController::class, 'backupDatabase'])->name('settings.backup');
    });

    // 2. Fitur Inventaris & Barang (Pemilik & Admin)
    Route::middleware('role:pemilik,admin')->group(function () {
        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
        Route::get('/stocks', [StockController::class, 'index'])->name('stocks.index');
        Route::get('/stocks/create', [StockController::class, 'create'])->name('stocks.create');
        Route::post('/stocks', [StockController::class, 'store'])->name('stocks.store');
    });

    // 3. Fitur Transaksi Penjualan / POS (Pemilik & Kasir)
    Route::middleware('role:pemilik,kasir')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::get('/pos/search', [PosController::class, 'search'])->name('pos.search');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
        Route::get('/pos/receipt/{order}', [PosController::class, 'receipt'])->name('pos.receipt');

        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/{order}', [TransactionController::class, 'show'])->name('transactions.show');
    });
});
