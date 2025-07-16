<?php

namespace App\Http\Controllers\Rekap;

use App\Http\Controllers\Controller;
use App\Models\Bulan;
use App\Models\KategoriBiaya;
use App\Models\RekapBiayaKesehatan;
use App\Models\BiayaTersedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

class RegionalController extends Controller
{
    public function index(Request $request)
    {
        $tahun = range(date('Y'), 2000);
        $selectedTahun = $request->tahun ?? null;
        $selectedBulan = $request->bulan ?? null;

        $bulan = Bulan::orderBy('id')->get();
        $kategori = KategoriBiaya::orderBy('id')->get();

        $grouped = [];
        $annualTotals = [];

        // Inisialisasi annualTotals untuk semua kategori dan total keseluruhan
        foreach ($kategori as $k) {
            $annualTotals[$k->id] = 0;
        }
        $annualTotals['all_kategoris_total'] = 0;

        if ($selectedTahun) {
            $rawData = RekapBiayaKesehatan::with(['kategoriBiaya', 'bulan'])
                ->where('tahun', $selectedTahun)
                ->get();

            foreach ($rawData as $row) {
                $bulanId = $row->bulan_id;
                $kategoriId = $row->kategori_biaya_id;

                if (!isset($grouped[$bulanId])) {
                    $grouped[$bulanId] = [
                        'id' => $row->id,
                        'bulan_id' => $bulanId,
                        'bulan' => $row->bulan->nama,
                        'tahun' => $row->tahun,
                        'validasi' => null,
                        'kategori' => [],
                        'total_biaya_kesehatan' => 0
                    ];
                }

                // Baris ini mengambil status validasi dari salah satu record untuk bulan tersebut.
                // Jika Anda memiliki banyak record untuk bulan yang sama, ini akan mengambil validasi dari record terakhir yang diproses.
                // Idealnya, status validasi ini harus disimpan di record 'total_biaya_kesehatan' yang kategori_biaya_id-nya NULL.

                $grouped[$bulanId]['validasi'] = $row->validasi ?? null;

                if ($kategoriId === null) {
                    // --- LOKASI PERUBAHAN PERTAMA ---
                    // Mengambil data untuk 'total_biaya_kesehatan' dari kolom 'total_biaya_kesehatan'
                    $grouped[$bulanId]['total_biaya_kesehatan'] = $row->total_biaya_kesehatan;
                    $annualTotals['all_kategoris_total'] += $row->total_biaya_kesehatan;
                } else {
                    $grouped[$bulanId]['kategori'][$kategoriId] = $row->total_biaya_kesehatan;
                    $annualTotals[$kategoriId] += $row->total_biaya_kesehatan;
                }
            }
            // Penting: Setelah loop selesai, urutkan $grouped berdasarkan bulan_id
            ksort($grouped);
        }

        $biayaTersedia = [];
        foreach ($kategori as $k) {
            $biayaTersedia[$k->id] = 0;
        }
        $biayaTersedia['all_kategoris_total'] = 0;

        if ($selectedTahun) {
            $rawBiayaTersedia = BiayaTersedia::where('tahun', $selectedTahun)->get();
            foreach ($rawBiayaTersedia as $bt) {
                if ($bt->kategori_biaya_id === null) {
                    $biayaTersedia['all_kategoris_total'] = $bt->total_tersedia;
                } else {
                    $biayaTersedia[$bt->kategori_biaya_id] = $bt->total_tersedia;
                }
            }
        }
        return view('rekap.regional', compact('bulan', 'tahun', 'selectedTahun', 'selectedBulan', 'kategori', 'grouped', 'annualTotals', 'biayaTersedia'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bulan_id' => 'required|exists:bulans,id',
            'tahun' => 'required|integer|min:2000|max:' . date('Y'),
            'total_biaya_kesehatan' => 'required|array',
            'total_biaya_kesehatan.*' => 'required|numeric|min:0',
        ]);
        $tahun = $request->tahun;
        $bulanId = $request->bulan_id;

        $kategoriCount = KategoriBiaya::count();
        $existingCount = RekapBiayaKesehatan::where('tahun', $tahun)
                                            ->where('bulan_id', $bulanId)
                                            ->whereNotNull('kategori_biaya_id')
                                            ->count();

        $existingTotalRecord = RekapBiayaKesehatan::where('tahun', $tahun)
                                                    ->where('bulan_id', $bulanId)
                                                    ->whereNull('kategori_biaya_id')
                                                    ->exists();

        if ($existingCount >= $kategoriCount && $existingTotalRecord) {
            return redirect()->route('rekap.regional.index', [
                'tahun' => $tahun,
                'bulan_id' => $bulanId,
            ])->with('success', '⚠️ Data input sudah pernah diinputkan, silakan cek tabel kembali.');
        }
        
        RekapBiayaKesehatan::where('tahun', $tahun)
                            ->where('bulan_id', $bulanId)
                            ->delete();

        $totalBiayaKesehatanBulanIni = 0;

        //Penyimpanan detail biaya
        foreach ($request->input('total_biaya_kesehatan') as $kategoriId => $valueInput) {
            $totalBiayaKesehatanDetail = (int) str_replace(['.', ','], '', $valueInput);

            RekapBiayaKesehatan::create([
                'kategori_biaya_id' => $kategoriId,
                'bulan_id' => $bulanId,
                'tahun' => $tahun,
                'total_biaya_kesehatan' => $totalBiayaKesehatanDetail,
                'validasi' => null,
            ]);
            $totalBiayaKesehatanBulanIni += $totalBiayaKesehatanDetail;
        }
        //Penyimpanan total biaya bulanan
        RekapBiayaKesehatan::create([
            'kategori_biaya_id' => null,
            'bulan_id' => $bulanId,
            'tahun' => $tahun,
            'total_biaya_kesehatan' => $totalBiayaKesehatanBulanIni,
            'validasi' => null,
        ]);

        return redirect()->route('rekap.regional.index', [
            'tahun' => $tahun,
            'bulan_id' => $bulanId,
        ])->with('success', '✅ Data berhasil disimpan!');
    }

    // Perbaikan: Ubah parameter $id menjadi $tahun dan $bulan_id
    // Ini akan mengubah cara Anda memanggil route update dari Blade.
    // Jika Anda ingin tetap menggunakan {id} di route, maka Anda harus mencari tahun dan bulan_id dari $id tersebut.
    // Saya akan asumsikan Anda akan mengubah Blade untuk mengirim tahun dan bulan_id.
    public function update(Request $request, $tahun, $bulan_id) // <--- UBAH PARAMETER DI SINI
    {
        $request->validate([
            'total_biaya_kesehatan' => 'required|array',
            'total_biaya_kesehatan.*' => 'required|numeric|min:0',
        ]);

        $totalRecord = RekapBiayaKesehatan::where('tahun', $tahun)
                                        ->where('bulan_id', $bulan_id)
                                        ->whereNull('kategori_biaya_id')
                                        ->first();

        if ($totalRecord && $totalRecord->validasi !== null) {
            return redirect()->route('rekap.regional.index', [
                'tahun' => $tahun,
                'bulan_id' => $bulan_id,
            ])->with('error', '⚠️ Data sudah tervalidasi dan tidak bisa diedit.');
        }

        $totalBiayaKesehatanBulanIni = 0;

        foreach ($request->input('total_biaya_kesehatan') as $kategoriId => $valueInput) {
            $totalBiayaKesehatanDetail = (int) str_replace(['.', ','], '', $valueInput);

            RekapBiayaKesehatan::updateOrCreate(
                [
                    'kategori_biaya_id' => $kategoriId,
                    'bulan_id' => $bulan_id,
                    'tahun' => $tahun,
                ],
                [
                    'total_biaya_kesehatan' => $totalBiayaKesehatanDetail,
                ]
            );
            $totalBiayaKesehatanBulanIni += $totalBiayaKesehatanDetail;
        }

        RekapBiayaKesehatan::updateOrCreate(
            [
                'kategori_biaya_id' => null,
                'bulan_id' => $bulan_id,
                'tahun' => $tahun,
            ],
            [
                'total_biaya_kesehatan' => $totalBiayaKesehatanBulanIni,
            ]
        );

        return redirect()->route('rekap.regional.index', [
            'tahun' => $tahun,
            'bulan' => $bulan_id,
        ])->with('success', '✅ Data berhasil diperbarui!');
    }

    public function destroy(Request $request, $tahun, $bulan_id)
    {
        $totalRecord = RekapBiayaKesehatan::where('tahun', $tahun)
                                        ->where('bulan_id', $bulan_id)
                                        ->whereNull('kategori_biaya_id')
                                        ->first();

        if ($totalRecord && $totalRecord->validasi !== null) {
            return redirect()->route('rekap.regional.index', [
                'tahun' => $tahun,
                'bulan' => $bulan_id,
            ])->with('error', '❌ Data sudah tervalidasi dan tidak bisa dihapus.');
        }

            RekapBiayaKesehatan::where('tahun', $tahun)
                            ->where('bulan_id', $bulan_id)
                            ->delete();

        return redirect()->route('rekap.regional.index', [
            'tahun' => $tahun,
        ])->with('success', '🗑️ Semua data untuk bulan ini berhasil dihapus.');
    }

    public function validateRekap(Request $request, $tahun, $bulan_id)
    {
        $totalRecord = RekapBiayaKesehatan::where('tahun', $tahun)
                                        ->where('bulan_id', $bulan_id)
                                        ->whereNull('kategori_biaya_id')
                                        ->firstOrFail();

        $totalRecord->validasi = 'Tervalidasi';
        $totalRecord->save();

        return redirect()->route('rekap.regional.index', [
            'tahun' => $tahun,
            'bulan' => $bulan_id,
        ])->with('success', '✅ Data berhasil divalidasi!');
    }


    //Controller Biaya Tersedia
    public function storeOrUpdateBiayaTersedia(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer|min:2000|max:' . date('Y'),
            'total_tersedia' => 'required|array',
            'total_tersedia.*' => 'required|numeric|min:0',
        ]);

        $tahun = $request->tahun;
        $totalBiayaTersedia = 0;

        // Simpan atau update biaya per kategori
        foreach ($request->input('total_tersedia') as $kategoriId => $valueInput) {
            $nilaiBiaya = (int) str_replace(['.', ','], '', $valueInput);

            BiayaTersedia::updateOrCreate(
                [
                    'tahun' => $tahun,
                    'kategori_biaya_id' => $kategoriId,
                ],
                [
                    'total_tersedia' => $nilaiBiaya,
                ]
            );
            $totalBiayaTersedia += $nilaiBiaya;
        }

        // Simpan atau update total biaya tersedia (dimana kategori_biaya_id adalah null)
        BiayaTersedia::updateOrCreate(
            [
                'tahun' => $tahun,
                'kategori_biaya_id' => null,
            ],
            [
                'total_tersedia' => $totalBiayaTersedia,
            ]
        );

    return redirect()->route('rekap.regional.index', ['tahun' => $tahun])
                    ->with('success', '✅ Data Biaya Tersedia berhasil diperbarui!');
    }

    /**
     * Menghapus semua data Biaya Tersedia untuk satu tahun.
     */
    public function destroyBiayaTersedia($tahun)
    {
        BiayaTersedia::where('tahun', $tahun)->delete();

        return redirect()->route('rekap.regional.index', ['tahun' => $tahun])
                        ->with('success', '🗑️ Data Biaya Tersedia berhasil dihapus.');
    }
}