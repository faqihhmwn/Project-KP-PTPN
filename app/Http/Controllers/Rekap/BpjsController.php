<?php

namespace App\Http\Controllers\Rekap;

use App\Http\Controllers\Controller;
use App\Models\Bulan;
use App\Models\KategoriIuran;
use App\Models\RekapBpjsIuran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

class BpjsController extends Controller
{
    public function index(Request $request)
    {
        $tahun = range(date('Y'), 2000);
        $selectedTahun = $request->tahun ?? null;
        $selectedBulan = $request->bulan ?? null;

        $bulan = Bulan::orderBy('id')->get();
        $kategori = KategoriIuran::orderBy('id')->get();

        $grouped = [];
        $annualTotals = [];

        // Inisialisasi annualTotals untuk semua kategori dan total keseluruhan
        foreach ($kategori as $k) {
            $annualTotals[$k->id] = 0;
        }
        $annualTotals['all_kategoris_total'] = 0;

        if ($selectedTahun) {
            $rawData = RekapBpjsIuran::with(['kategoriIuran', 'bulan'])
                ->where('tahun', $selectedTahun)
                ->get();

            foreach ($rawData as $row) {
                $bulanId = $row->bulan_id;
                $kategoriId = $row->kategori_iuran_id;

                if (!isset($grouped[$bulanId])) {
                    $grouped[$bulanId] = [
                        'id' => $row->id,
                        'bulan_id' => $bulanId,
                        'bulan' => $row->bulan->nama,
                        'tahun' => $row->tahun,
                        'validasi' => null,
                        'kategori' => [],
                        'total_iuran_bpjs' => 0
                    ];
                }

                $grouped[$bulanId]['validasi'] = $row->validasi ?? null;

                if ($kategoriId === null) {
                    $grouped[$bulanId]['total_iuran_bpjs'] = $row->total_iuran_bpjs;
                    $annualTotals['all_kategoris_total'] += $row->total_iuran_bpjs;
                } else {
                    $grouped[$bulanId]['kategori'][$kategoriId] = $row->total_iuran_bpjs;
                    $annualTotals[$kategoriId] += $row->total_iuran_bpjs;
                }

            // Penting: Setelah loop selesai, urutkan $grouped berdasarkan bulan_id
            }
            ksort($grouped);
    }
    return view('rekap.bpjs', compact('bulan', 'tahun', 'selectedTahun', 'selectedBulan', 'kategori', 'grouped', 'annualTotals'));

}

    public function store(Request $request)
    {
        $request->validate([
            'bulan_id' => 'required|exists:bulans,id',
            'tahun' => 'required|integer|min:2000|max:' . date('Y'),
            'total_iuran_bpjs' => 'required|array',
            'total_iuran_bpjs.*' => 'required|numeric|min:0',
        ]);
        $tahun = $request->tahun;
        $bulanId = $request->bulan_id;

        $kategoriCount = KategoriIuran::count();
        $existingCount = RekapBpjsIuran::where('tahun', $tahun)
                                            ->where('bulan_id', $bulanId)
                                            ->whereNotNull('kategori_iuran_id')
                                            ->count();

        $existingTotalRecord = RekapBpjsIuran::where('tahun', $tahun)
                                                    ->where('bulan_id', $bulanId)
                                                    ->whereNull('kategori_iuran_id')
                                                    ->exists();

        if ($existingCount >= $kategoriCount && $existingTotalRecord) {
            return redirect()->route('rekap.bpjs.index', [
                'tahun' => $tahun,
                'bulan_id' => $bulanId,
            ])->with('success', '⚠️ Data input sudah pernah diinputkan, silakan cek tabel kembali.');
        }
        
        RekapBpjsIuran::where('tahun', $tahun)
                            ->where('bulan_id', $bulanId)
                            ->delete();

        $totalIuranBpjsBulanIni = 0;

        //Penyimpanan detail biaya
        foreach ($request->input('total_iuran_bpjs') as $kategoriId => $valueInput) {
            $totalIuranBpjsDetail = (int) $valueInput;

            RekapBpjsIuran::create([
                'kategori_iuran_id' => $kategoriId,
                'bulan_id' => $bulanId,
                'tahun' => $tahun,
                'total_iuran_bpjs' => $totalIuranBpjsDetail,
                'validasi' => null,
            ]);
            $totalIuranBpjsBulanIni += $totalIuranBpjsDetail;
        }
        //Penyimpanan total biaya bulanan
        RekapBpjsIuran::create([
            'kategori_iuran_id' => null,
            'bulan_id' => $bulanId,
            'tahun' => $tahun,
            'total_iuran_bpjs' => $totalIuranBpjsBulanIni,
            'validasi' => null,
        ]);

        return redirect()->route('rekap.bpjs.index', [
            'tahun' => $tahun,
            'bulan_id' => $bulanId,
        ])->with('success', '✅ Data berhasil disimpan!');
    }

    // Perbaikan: Ubah parameter $id menjadi $tahun dan $bulan_id
    public function update(Request $request, $tahun, $bulan_id)
    {
        $request->validate([
            'total_iuran_bpjs' => 'required|array',
            'total_iuran_bpjs.*' => 'required|numeric|min:0',
        ]);

        $totalRecord = RekapBpjsIuran::where('tahun', $tahun)
                                        ->where('bulan_id', $bulan_id)
                                        ->whereNull('kategori_iuran_id')
                                        ->first();

        if ($totalRecord && $totalRecord->validasi !== null) {
            return redirect()->route('rekap.bpjs.index', [
                'tahun' => $tahun,
                'bulan_id' => $bulan_id,
            ])->with('error', '⚠️ Data sudah tervalidasi dan tidak bisa diedit.');
        }

        $totalIuranBpjsBulanIni = 0;

        foreach ($request->input('total_iuran_bpjs') as $kategoriId => $valueInput) {
            $totalIuranBpjsDetail = (int) $valueInput;

            RekapBpjsIuran::updateOrCreate(
                [
                    'kategori_iuran_id' => $kategoriId,
                    'bulan_id' => $bulan_id,
                    'tahun' => $tahun,
                ],
                [
                    'total_iuran_bpjs' => $totalIuranBpjsDetail,
                ]
            );
            $totalIuranBpjsBulanIni += $totalIuranBpjsDetail;
        }

        RekapBpjsIuran::updateOrCreate(
            [
                'kategori_iuran_id' => null,
                'bulan_id' => $bulan_id,
                'tahun' => $tahun,
            ],
            [
                'total_iuran_bpjs' => $totalIuranBpjsBulanIni,
            ]
        );

        return redirect()->route('rekap.bpjs.index', [
            'tahun' => $tahun,
            'bulan' => $bulan_id,
        ])->with('success', '✅ Data berhasil diperbarui!');
    }

    public function destroy(Request $request, $tahun, $bulan_id)
    {
        $totalRecord = RekapBpjsIuran::where('tahun', $tahun)
                                        ->where('bulan_id', $bulan_id)
                                        ->whereNull('kategori_iuran_id')
                                        ->first();

        if ($totalRecord && $totalRecord->validasi !== null) {
            return redirect()->route('rekap.bpjs.index', [
                'tahun' => $tahun,
                'bulan' => $bulan_id,
            ])->with('error', '❌ Data sudah tervalidasi dan tidak bisa dihapus.');
        }

            RekapBpjsIuran::where('tahun', $tahun)
                            ->where('bulan_id', $bulan_id)
                            ->delete();

        return redirect()->route('rekap.bpjs.index', [
            'tahun' => $tahun,
        ])->with('success', '🗑️ Semua data untuk bulan ini berhasil dihapus.');
    }

    public function validateRekap(Request $request, $tahun, $bulan_id)
    {
        $totalRecord = RekapBpjsIuran::where('tahun', $tahun)
                                        ->where('bulan_id', $bulan_id)
                                        ->whereNull('kategori_iuran_id')
                                        ->firstOrFail();

        $totalRecord->validasi = 'Tervalidasi';
        $totalRecord->save();

        return redirect()->route('rekap.bpjs.index', [
            'tahun' => $tahun,
            'bulan' => $bulan_id,
        ])->with('success', '✅ Data berhasil divalidasi!');
    }
}