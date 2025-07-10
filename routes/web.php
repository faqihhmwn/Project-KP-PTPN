<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RekapBiayaController;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/login', function () {
    return view('login');
});


Route::get('/content/rekap-biaya', function () {
    return redirect()->route('rekap-biaya.show');
});

// ROUTE UNTUK REKAP BIAYA KESEHATAN
Route::get('/rekap-biaya', [RekapBiayaController::class, 'filterForm'])->name('rekap.form');
Route::get('/rekap-biaya/show', [RekapBiayaController::class, 'show'])->name('rekap.show');
Route::post('/rekap-biaya/store', [RekapBiayaController::class, 'store'])->name('rekap.store');
Route::get('/rekap-biaya/export', [RekapBiayaController::class, 'export'])->name('rekap.export');
