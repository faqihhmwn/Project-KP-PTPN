<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Laporan\KependudukanController;
use App\Http\Controllers\Laporan\KonsultasiKlinikController;
use App\Http\Controllers\Laporan\OpnameController;
use App\Http\Controllers\Laporan\PenyakitController;
use App\Http\Controllers\Laporan\PenyakitKronisController;
use App\Http\Controllers\Laporan\CutiSakitController;
use App\Http\Controllers\Laporan\PesertaKbController;
use App\Http\Controllers\Laporan\MetodeKbController;
use App\Http\Controllers\Laporan\KehamilanController;
use App\Http\Controllers\Laporan\ImunisasiController;
use App\Http\Controllers\Laporan\KematianController;
use App\Http\Controllers\Laporan\KlaimAsuransiController;
use App\Http\Controllers\Laporan\KecelakaanKerjaController;
use App\Http\Controllers\Laporan\SakitBerkepanjanganController;
use App\Http\Controllers\Laporan\AbsensiDokterHonorController;
use App\Http\Controllers\Laporan\KategoriKhususController;
use App\Http\Controllers\ObatController;
use App\Http\Controllers\RekapitulasiObatController;
use App\Http\Controllers\RekapitulasiExportController;
use App\Http\Controllers\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth:web,admin')
    ->name('dashboard');

// Route untuk export obat
Route::get('/obat/export', [RekapitulasiExportController::class, 'export'])->name('obat.export');

Route::get('/', function () {
    return redirect('/dashboard');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// web.php
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::put('/password', [\App\Http\Controllers\PasswordController::class, 'update'])->middleware('auth')->name('password.update');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

require __DIR__.'/auth.php';

Route::prefix('laporan/kependudukan')->middleware('auth:web,admin')->name('laporan.kependudukan.')->group(function () {
    Route::get('/', [KependudukanController::class, 'index'])->name('index');
    Route::post('/store', [KependudukanController::class, 'store'])->name('store');
    Route::put('/update/{id}', [KependudukanController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [KependudukanController::class, 'destroy'])->name('destroy');
    Route::post('/approve', [KependudukanController::class, 'approve'])->name('approve');
    Route::post('/unapprove', [KependudukanController::class, 'unapprove'])->name('unapprove');
});

Route::prefix('laporan/penyakit')->middleware('auth:web,admin')->name('laporan.penyakit.')->group(function () {
    Route::get('/', [PenyakitController::class, 'index'])->name('index');
    Route::post('/store', [PenyakitController::class, 'store'])->name('store');
    Route::put('/update/{id}', [PenyakitController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [PenyakitController::class, 'destroy'])->name('destroy');
    Route::post('/approve', [PenyakitController::class, 'approve'])->name('approve');
    Route::post('/unapprove', [PenyakitController::class, 'unapprove'])->name('unapprove');
});

Route::prefix('laporan/opname')->middleware('auth:web,admin')->name('laporan.opname.')->group(function () {
    Route::get('/', [OpnameController::class, 'index'])->name('index');
    Route::post('/store', [OpnameController::class, 'store'])->name('store');
    Route::put('/update/{id}', [OpnameController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [OpnameController::class, 'destroy'])->name('destroy');
    Route::post('/approve', [OpnameController::class, 'approve'])->name('approve');
    Route::post('/unapprove', [OpnameController::class, 'unapprove'])->name('unapprove');
});

Route::prefix('laporan/penyakit-kronis')->middleware('auth:web,admin')->name('laporan.penyakit-kronis.')->group(function () {
    Route::get('/', [PenyakitKronisController::class, 'index'])->name('index');
    Route::post('/store', [PenyakitKronisController::class, 'store'])->name('store');
    Route::put('/update/{id}', [PenyakitKronisController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [PenyakitKronisController::class, 'destroy'])->name('destroy');
    Route::post('/approve', [PenyakitKronisController::class, 'approve'])->name('approve');
    Route::post('/unapprove', [PenyakitKronisController::class, 'unapprove'])->name('unapprove');
});

Route::prefix('laporan/konsultasi-klinik')->middleware('auth:web,admin')->name('laporan.konsultasi-klinik.')->group(function () {
    Route::get('/', [KonsultasiKlinikController::class, 'index'])->name('index');
    Route::post('/store', [KonsultasiKlinikController::class, 'store'])->name('store');
    Route::put('/update/{id}', [KonsultasiKlinikController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [KonsultasiKlinikController::class, 'destroy'])->name('destroy');
    Route::post('/approve', [KonsultasiKlinikController::class, 'approve'])->name('approve');
    Route::post('/unapprove', [KonsultasiKlinikController::class, 'unapprove'])->name('unapprove');
});

Route::prefix('laporan/cuti-sakit')->middleware('auth:web,admin')->name('laporan.cuti-sakit.')->group(function () {
    Route::get('/', [CutiSakitController::class, 'index'])->name('index');
    Route::post('/store', [CutiSakitController::class, 'store'])->name('store');
    Route::put('/update/{id}', [CutiSakitController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [CutiSakitController::class, 'destroy'])->name('destroy');
    Route::post('/approve', [CutiSakitController::class, 'approve'])->name('approve');
    Route::post('/unapprove', [CutiSakitController::class, 'unapprove'])->name('unapprove');
});

Route::prefix('laporan/peserta-kb')->middleware('auth:web,admin')->name('laporan.peserta-kb.')->group(function () {
    Route::get('/', [PesertaKbController::class, 'index'])->name('index');
    Route::post('/store', [PesertaKbController::class, 'store'])->name('store');
    Route::put('/update/{id}', [PesertaKbController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [PesertaKbController::class, 'destroy'])->name('destroy');
    Route::post('/approve', [PesertaKbController::class, 'approve'])->name('approve');
    Route::post('/unapprove', [PesertaKbController::class, 'unapprove'])->name('unapprove');
});

Route::prefix('laporan/metode-kb')->middleware('auth:web,admin')->name('laporan.metode-kb.')->group(function () {
    Route::get('/', [MetodeKbController::class, 'index'])->name('index');
    Route::post('/store', [MetodeKbController::class, 'store'])->name('store');
    Route::put('/update/{id}', [MetodeKbController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [MetodeKbController::class, 'destroy'])->name('destroy');
    Route::post('/approve', [MetodeKbController::class, 'approve'])->name('approve');
    Route::post('/unapprove', [MetodeKbController::class, 'unapprove'])->name('unapprove');
});

Route::prefix('laporan/kehamilan')->middleware('auth:web,admin')->name('laporan.kehamilan.')->group(function () {
    Route::get('/', [KehamilanController::class, 'index'])->name('index');
    Route::post('/store', [KehamilanController::class, 'store'])->name('store');
    Route::put('/update/{id}', [KehamilanController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [KehamilanController::class, 'destroy'])->name('destroy');
    Route::post('/approve', [KehamilanController::class, 'approve'])->name('approve');
    Route::post('/unapprove', [KehamilanController::class, 'unapprove'])->name('unapprove');
});

Route::prefix('laporan/imunisasi')->middleware('auth:web,admin')->name('laporan.imunisasi.')->group(function () {
    Route::get('/', [ImunisasiController::class, 'index'])->name('index');
    Route::post('/store', [ImunisasiController::class, 'store'])->name('store');
    Route::put('/update/{id}', [ImunisasiController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [ImunisasiController::class, 'destroy'])->name('destroy');
    Route::post('/approve', [ImunisasiController::class, 'approve'])->name('approve');
    Route::post('/unapprove', [ImunisasiController::class, 'unapprove'])->name('unapprove');
});

Route::prefix('laporan/kematian')->middleware('auth:web,admin')->name('laporan.kematian.')->group(function () {
    Route::get('/', [KematianController::class, 'index'])->name('index');
    Route::post('/store', [KematianController::class, 'store'])->name('store');
    Route::put('/update/{id}', [KematianController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [KematianController::class, 'destroy'])->name('destroy');
    Route::post('/approve', [KematianController::class, 'approve'])->name('approve');
    Route::post('/unapprove', [KematianController::class, 'unapprove'])->name('unapprove');
});

Route::prefix('laporan/klaim-asuransi')->middleware('auth:web,admin')->name('laporan.klaim-asuransi.')->group(function () {
    Route::get('/', [KlaimAsuransiController::class, 'index'])->name('index');
    Route::post('/store', [KlaimAsuransiController::class, 'store'])->name('store');
    Route::put('/update/{id}', [KlaimAsuransiController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [KlaimAsuransiController::class, 'destroy'])->name('destroy');
    Route::post('/approve', [KlaimAsuransiController::class, 'approve'])->name('approve');
    Route::post('/unapprove', [KlaimAsuransiController::class, 'unapprove'])->name('unapprove');
});

Route::prefix('laporan/kecelakaan-kerja')->middleware('auth:web,admin')->name('laporan.kecelakaan-kerja.')->group(function () {
    Route::get('/', [KecelakaanKerjaController::class, 'index'])->name('index');
    Route::post('/store', [KecelakaanKerjaController::class, 'store'])->name('store');
    Route::put('/update/{id}', [KecelakaanKerjaController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [KecelakaanKerjaController::class, 'destroy'])->name('destroy');
    Route::post('/approve', [KecelakaanKerjaController::class, 'approve'])->name('approve');
    Route::post('/unapprove', [KecelakaanKerjaController::class, 'unapprove'])->name('unapprove');
});

Route::prefix('laporan/sakit-berkepanjangan')->middleware('auth:web,admin')->name('laporan.sakit-berkepanjangan.')->group(function () {
    Route::get('/', [SakitBerkepanjanganController::class, 'index'])->name('index');
    Route::post('/store', [SakitBerkepanjanganController::class, 'store'])->name('store');
    Route::put('/update/{id}', [SakitBerkepanjanganController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [SakitBerkepanjanganController::class, 'destroy'])->name('destroy');
    Route::post('/approve', [SakitBerkepanjanganController::class, 'approve'])->name('approve');
    Route::post('/unapprove', [SakitBerkepanjanganController::class, 'unapprove'])->name('unapprove');
});

Route::prefix('laporan/absensi-dokter-honorer')->middleware('auth:web,admin')->name('laporan.absensi-dokter-honorer.')->group(function () {
    Route::get('/', [AbsensiDokterHonorController::class, 'index'])->name('index');
    Route::post('/store', [AbsensiDokterHonorController::class, 'store'])->name('store');
    Route::put('/update/{id}', [AbsensiDokterHonorController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [AbsensiDokterHonorController::class, 'destroy'])->name('destroy');
    Route::post('/approve', [AbsensiDokterHonorController::class, 'approve'])->name('approve');
    Route::post('/unapprove', [AbsensiDokterHonorController::class, 'unapprove'])->name('unapprove');
});

Route::prefix('laporan/kategori-khusus')->middleware('auth:web,admin')->name('laporan.kategori-khusus.')->group(function () {
    Route::get('/', [KategoriKhususController::class, 'index'])->name('index');
    Route::post('/store', [KategoriKhususController::class, 'store'])->name('store');
    Route::put('/update/{id}', [KategoriKhususController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [KategoriKhususController::class, 'destroy'])->name('destroy');
    Route::post('/approve/', [KategoriKhususController::class, 'approve'])->name('approve');
    Route::post('/unapprove/', [KategoriKhususController::class, 'unapprove'])->name('unapprove');
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
    Route::post('/rekapitulasi-obat/input-harian', [RekapitulasiObatController::class, 'storeOrUpdate'])->name('rekapitulasi-obat.input-harian');
    Route::get('/rekapitulasi/bulanan', [ObatController::class, 'rekapitulasi'])->name('rekapitulasi');
    Route::get('/export', [RekapitulasiExportController::class, 'export'])->name('export');
    Route::get('/{obat}/rekapitulasi', [ObatController::class, 'showRekapitulasi'])->name('rekapitulasi.detail');
    
    // Transaksi
    Route::post('/{obat}/transaksi', [ObatController::class, 'addTransaksi'])->name('transaksi.store');
    Route::post('/{obat}/transaksi-harian', [ObatController::class, 'updateTransaksiHarian'])->name('transaksi.harian');
    
    // Import
    Route::post('/import', [ObatController::class, 'import'])->name('import');
});



