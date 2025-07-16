
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ObatController;
use App\Http\Controllers\RekapitulasiObatController;

Route::get('/', function () {
    return view('dashboard');
});


// Obat Routes
Route::prefix('obat')->name('obat.')->group(function () {
    Route::get('/', [ObatController::class, 'index'])->name('index');
    Route::get('/dashboard', [ObatController::class, 'dashboard'])->name('dashboard');
    Route::get('/create', [ObatController::class, 'create'])->name('create');
    Route::post('/', [ObatController::class, 'store'])->name('store');
    Route::get('/{obat}', [ObatController::class, 'show'])->name('show');
    Route::get('/{obat}/edit', [ObatController::class, 'edit'])->name('edit');
    Route::put('/{obat}', [ObatController::class, 'update'])->name('update');
    Route::delete('/{obat}', [ObatController::class, 'destroy'])->name('destroy');
    
    // Rekapitulasi
    Route::post('/rekapitulasi-obat/input-harian', [ObatController::class, 'storeRekapitulasi'])->name('rekapitulasi-obat.input-harian');
    Route::get('/rekapitulasi/bulanan', [ObatController::class, 'rekapitulasi'])->name('rekapitulasi');
    
    // Transaksi
    Route::post('/{obat}/transaksi', [ObatController::class, 'addTransaksi'])->name('transaksi.store');
    Route::post('/{obat}/transaksi-harian', [ObatController::class, 'updateTransaksiHarian'])->name('transaksi.harian');
    
    // Import/Export
    Route::post('/import', [ObatController::class, 'import'])->name('import');
    Route::get('/export', [ObatController::class, 'exportExcel'])->name('export');
});


// Farmasi Sidebar Routes
// Route::prefix('farmasi')->group(function () {
//     Route::get('/dashboard-obat', [ObatController::class, 'dashboard']);
//     Route::get('/rekapitulasi-obat', [ObatController::class, 'rekapitulasi']);
// });

// Default redirect ke dashboard obat
// Route::redirect('/dashboard', '/obat/dashboard');


Route::get('/login', function () {
    return view('login');
});