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
use App\Http\Controllers\Admin\ObatController as AdminObatController;
use App\Http\Controllers\Admin\RekapitulasiObatController as AdminRekapitulasiObatController;
use App\Http\Controllers\Admin\RekapitulasiExportController as AdminRekapitulasiExportController;


use App\Http\Controllers\Rekap\RegionalController;
use App\Http\Controllers\Rekap\KapitasiController;
use App\Http\Controllers\Rekap\BpjsController;
use App\Http\Controllers\Rekap\SisaSaldoController;

use App\Http\Controllers\Rekap\RekapBiayaKesehatanExportController;
use App\Http\Controllers\Rekap\BpjsExportController;
use App\Http\Controllers\Rekap\KapitasiExportController;


Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth:web,admin')
    ->name('dashboard');

Route::get('/dashboard/export-rekap', [App\Http\Controllers\DashboardController::class, 'exportRekap'])
    ->middleware('auth:admin')
    ->name('dashboard.export-rekap');

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
    
    Route::get('/export', [KependudukanController::class, 'export'])->name('export');
});

Route::prefix('laporan/penyakit')->middleware('auth:web,admin')->name('laporan.penyakit.')->group(function () {
    Route::get('/', [PenyakitController::class, 'index'])->name('index');
    Route::post('/store', [PenyakitController::class, 'store'])->name('store');
    Route::put('/update/{id}', [PenyakitController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [PenyakitController::class, 'destroy'])->name('destroy');
    Route::post('/approve', [PenyakitController::class, 'approve'])->name('approve');
    Route::post('/unapprove', [PenyakitController::class, 'unapprove'])->name('unapprove');

    Route::get('/export', [PenyakitController::class, 'export'])->name('export');
});

Route::prefix('laporan/opname')->middleware('auth:web,admin')->name('laporan.opname.')->group(function () {
    Route::get('/', [OpnameController::class, 'index'])->name('index');
    Route::post('/store', [OpnameController::class, 'store'])->name('store');
    Route::put('/update/{id}', [OpnameController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [OpnameController::class, 'destroy'])->name('destroy');
    Route::post('/approve', [OpnameController::class, 'approve'])->name('approve');
    Route::post('/unapprove', [OpnameController::class, 'unapprove'])->name('unapprove');

    Route::get('/export', [OpnameController::class, 'export'])->name('export');
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

// REKAPITULASI BIAYA
Route::prefix('rekap')->middleware('auth')->name('rekap.')->group(function () {

    // Regional
    Route::prefix('regional')->name('regional.')->group(function () {
        Route::get('/', [RegionalController::class, 'index'])->name('index');
        Route::post('/', [RegionalController::class, 'store'])->name('store');
        Route::put('/{tahun}/{bulan_id}', [RegionalController::class, 'update'])->name('update');
        Route::delete('/{tahun}/{bulan_id}', [RegionalController::class, 'destroy'])->name('destroy');
        Route::put('/{tahun}/{bulan_id}/validate', [RegionalController::class, 'validateRekap'])->name('validate');
        Route::post('biaya-tersedia', [RegionalController::class, 'storeOrUpdateBiayaTersedia'])->name('biayaTersedia.storeOrUpdate');
        Route::delete('biaya-tersedia/{tahun}', [RegionalController::class, 'destroyBiayaTersedia'])->name('biayaTersedia.destroy');
        Route::get('/rekap-biaya-kesehatan/export', [RekapBiayaKesehatanExportController::class, 'export'])->name('biayakesehatan.export');
    });

    // BPJS
    Route::prefix('bpjs')->name('bpjs.')->group(function () {
        Route::get('/', [BpjsController::class, 'index'])->name('index');
        Route::post('/', [BpjsController::class, 'store'])->name('store');
        Route::put('/{tahun}/{bulan_id}', [BpjsController::class, 'update'])->name('update');
        Route::delete('/{tahun}/{bulan_id}', [BpjsController::class, 'destroy'])->name('destroy');
        Route::put('/{tahun}/{bulan_id}/validate', [BpjsController::class, 'validateRekap'])->name('validate');
        Route::get('/bpjs/export', [BpjsExportController::class, 'export'])->name('bpjs.export');

    });

    // Kapitasi
    Route::prefix('kapitasi')->name('kapitasi.')->group(function () {
        Route::put('/saldo-awal/{tahun}', [KapitasiController::class, 'updateSaldoAwal'])->name('updateSaldoAwal');
        Route::get('/', [KapitasiController::class, 'index'])->name('index');
        Route::post('/', [KapitasiController::class, 'store'])->name('store');
        Route::put('/{tahun}/{bulan_id}', [KapitasiController::class, 'update'])->name('update');
        Route::delete('/{tahun}/{bulan_id}', [KapitasiController::class, 'destroy'])->name('destroy');
        Route::put('/{tahun}/{bulan_id}/validate', [KapitasiController::class, 'validateRekap'])->name('validate');
        Route::get('/kapitasi/export', [KapitasiExportController::class, 'export'])->name('kapitasi.export');

    });
});

// Admin Obat Routes
Route::prefix('admin/obat')->name('admin.obat.')->middleware('auth:admin')->group(function () {
    Route::get('/', [AdminObatController::class, 'index'])->name('index');
    Route::get('/dashboard', [AdminObatController::class, 'dashboard'])->name('dashboard');
    Route::get('/create', [AdminObatController::class, 'create'])->name('create');
    Route::post('/', [AdminObatController::class, 'store'])->name('store');
    Route::get('/{obat}', [AdminObatController::class, 'show'])->name('show');
    Route::get('/{obat}/edit', [AdminObatController::class, 'edit'])->name('edit');
    Route::put('/{obat}', [AdminObatController::class, 'update'])->name('update');
    Route::delete('/{obat}', [AdminObatController::class, 'destroy'])->name('destroy');

    // Rekapitulasi
    Route::post('/rekapitulasi-obat/input-harian', [AdminRekapitulasiObatController::class, 'storeOrUpdate'])->name('rekapitulasi-obat.input-harian');
    Route::get('/rekapitulasi/bulanan', [AdminObatController::class, 'rekapitulasi'])->name('rekapitulasi');
    Route::get('/export', [AdminRekapitulasiExportController::class, 'export'])->name('export');
    Route::get('/{obat}/rekapitulasi', [AdminObatController::class, 'showRekapitulasi'])->name('rekapitulasi.detail');

    // Transaksi
    Route::post('/{obat}/transaksi', [AdminObatController::class, 'addTransaksi'])->name('transaksi.store');
    Route::post('/{obat}/transaksi-harian', [AdminObatController::class, 'updateTransaksiHarian'])->name('transaksi.harian');

    // Import
    Route::post('/import', [AdminObatController::class, 'import'])->name('import');
});
