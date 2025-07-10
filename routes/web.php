<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RekapBiayaController;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/login', function () {
    return view('login');
});


Route::get('/content/rekap-biaya', [RekapBiayaController::class, 'index'])->name('rekap-biaya.index');
Route::post('/content/rekap-biaya', [RekapBiayaController::class, 'store'])->name('rekap-biaya.store');
Route::get('/content/rekap-biaya/export', [RekapBiayaController::class, 'export'])->name('rekap-biaya.export');