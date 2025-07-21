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

use App\Http\Controllers\Rekap\RegionalController;
use App\Http\Controllers\Rekap\KapitasiController;
use App\Http\Controllers\Rekap\BpjsController;
use App\Http\Controllers\Rekap\SisaSaldoController;



Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile-edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile-update', [ProfileController::class, 'update'])->name('profile.update');
});
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

require __DIR__.'/auth.php';

Route::prefix('laporan/kependudukan')->middleware('auth')->name('laporan.kependudukan.')->group(function () {
    Route::get('/', [KependudukanController::class, 'index'])->name('index');
    Route::post('/store', [KependudukanController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [KependudukanController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [KependudukanController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [KependudukanController::class, 'destroy'])->name('destroy');
});

Route::prefix('laporan/penyakit')->middleware('auth')->name('laporan.penyakit.')->group(function () {
    Route::get('/', [PenyakitController::class, 'index'])->name('index');
    Route::post('/store', [PenyakitController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [PenyakitController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [PenyakitController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [PenyakitController::class, 'destroy'])->name('destroy');
});

Route::prefix('laporan/opname')->middleware('auth')->name('laporan.opname.')->group(function () {
    Route::get('/', [OpnameController::class, 'index'])->name('index');
    Route::post('/store', [OpnameController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [OpnameController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [OpnameController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [OpnameController::class, 'destroy'])->name('destroy');
});

Route::prefix('laporan/penyakit-kronis')->middleware('auth')->name('laporan.penyakit-kronis.')->group(function () {
    Route::get('/', [PenyakitKronisController::class, 'index'])->name('index');
    Route::post('/store', [PenyakitKronisController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [PenyakitKronisController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [PenyakitKronisController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [PenyakitKronisController::class, 'destroy'])->name('destroy');
});

Route::prefix('laporan/konsultasi-klinik')->middleware('auth')->name('laporan.konsultasi-klinik.')->group(function () {
    Route::get('/', [KonsultasiKlinikController::class, 'index'])->name('index');
    Route::post('/store', [KonsultasiKlinikController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [KonsultasiKlinikController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [KonsultasiKlinikController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [KonsultasiKlinikController::class, 'destroy'])->name('destroy');
});

Route::prefix('laporan/cuti-sakit')->middleware('auth')->name('laporan.cuti-sakit.')->group(function () {
    Route::get('/', [CutiSakitController::class, 'index'])->name('index');
    Route::post('/store', [CutiSakitController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [CutiSakitController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [CutiSakitController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [CutiSakitController::class, 'destroy'])->name('destroy');
});

Route::prefix('laporan/peserta-kb')->middleware('auth')->name('laporan.peserta-kb.')->group(function () {
    Route::get('/', [PesertaKbController::class, 'index'])->name('index');
    Route::post('/store', [PesertaKbController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [PesertaKbController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [PesertaKbController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [PesertaKbController::class, 'destroy'])->name('destroy');
});

Route::prefix('laporan/metode-kb')->middleware('auth')->name('laporan.metode-kb.')->group(function () {
    Route::get('/', [MetodeKbController::class, 'index'])->name('index');
    Route::post('/store', [MetodeKbController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [MetodeKbController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [MetodeKbController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [MetodeKbController::class, 'destroy'])->name('destroy');
});

Route::prefix('laporan/kehamilan')->middleware('auth')->name('laporan.kehamilan.')->group(function () {
    Route::get('/', [KehamilanController::class, 'index'])->name('index');
    Route::post('/store', [KehamilanController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [KehamilanController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [KehamilanController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [KehamilanController::class, 'destroy'])->name('destroy');
});

Route::prefix('laporan/imunisasi')->middleware('auth')->name('laporan.imunisasi.')->group(function () {
    Route::get('/', [ImunisasiController::class, 'index'])->name('index');
    Route::post('/store', [ImunisasiController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [ImunisasiController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [ImunisasiController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [ImunisasiController::class, 'destroy'])->name('destroy');
});

Route::prefix('laporan/kematian')->middleware('auth')->name('laporan.kematian.')->group(function () {
    Route::get('/', [KematianController::class, 'index'])->name('index');
    Route::post('/store', [KematianController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [KematianController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [KematianController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [KematianController::class, 'destroy'])->name('destroy');
});

Route::prefix('laporan/klaim-asuransi')->middleware('auth')->name('laporan.klaim-asuransi.')->group(function () {
    Route::get('/', [KlaimAsuransiController::class, 'index'])->name('index');
    Route::post('/store', [KlaimAsuransiController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [KlaimAsuransiController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [KlaimAsuransiController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [KlaimAsuransiController::class, 'destroy'])->name('destroy');
});

Route::prefix('laporan/kecelakaan-kerja')->middleware('auth')->name('laporan.kecelakaan-kerja.')->group(function () {
    Route::get('/', [KecelakaanKerjaController::class, 'index'])->name('index');
    Route::post('/store', [KecelakaanKerjaController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [KecelakaanKerjaController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [KecelakaanKerjaController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [KecelakaanKerjaController::class, 'destroy'])->name('destroy');
});

Route::prefix('laporan/sakit-berkepanjangan')->middleware('auth')->name('laporan.sakit-berkepanjangan.')->group(function () {
    Route::get('/', [SakitBerkepanjanganController::class, 'index'])->name('index');
    Route::post('/store', [SakitBerkepanjanganController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [SakitBerkepanjanganController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [SakitBerkepanjanganController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [SakitBerkepanjanganController::class, 'destroy'])->name('destroy');
});

Route::prefix('laporan/absensi-dokter-honorer')->middleware('auth')->name('laporan.absensi-dokter-honorer.')->group(function () {
    Route::get('/', [AbsensiDokterHonorController::class, 'index'])->name('index');
    Route::post('/store', [AbsensiDokterHonorController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [AbsensiDokterHonorController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [AbsensiDokterHonorController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [AbsensiDokterHonorController::class, 'destroy'])->name('destroy');
});

Route::prefix('laporan/kategori-khusus')->middleware('auth')->name('laporan.kategori-khusus.')->group(function () {
    Route::get('/', [KategoriKhususController::class, 'index'])->name('index');
    Route::post('/store', [KategoriKhususController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [KategoriKhususController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [KategoriKhususController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [KategoriKhususController::class, 'destroy'])->name('destroy');
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
    });

    // BPJS
    Route::prefix('bpjs')->name('bpjs.')->group(function () {
        Route::get('/', [BpjsController::class, 'index'])->name('index');
        Route::post('/', [BpjsController::class, 'store'])->name('store');
        Route::put('/{tahun}/{bulan_id}', [BpjsController::class, 'update'])->name('update');
        Route::delete('/{tahun}/{bulan_id}', [BpjsController::class, 'destroy'])->name('destroy');
        Route::put('/{tahun}/{bulan_id}/validate', [BpjsController::class, 'validateRekap'])->name('validate');
    });

    // Kapitasi
    Route::prefix('kapitasi')->name('kapitasi.')->group(function () {
        Route::put('/saldo-awal/{tahun}', [KapitasiController::class, 'updateSaldoAwal'])->name('updateSaldoAwal');
        Route::get('/', [KapitasiController::class, 'index'])->name('index');
        Route::post('/', [KapitasiController::class, 'store'])->name('store');
        Route::put('/{tahun}/{bulan_id}', [KapitasiController::class, 'update'])->name('update');
        Route::delete('/{tahun}/{bulan_id}', [KapitasiController::class, 'destroy'])->name('destroy');
        Route::put('/{tahun}/{bulan_id}/validate', [KapitasiController::class, 'validateRekap'])->name('validate');
    });
});
