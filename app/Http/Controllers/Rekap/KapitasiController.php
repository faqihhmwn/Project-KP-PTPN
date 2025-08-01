<?php

namespace App\Http\Controllers\Rekap;

use App\Http\Controllers\Controller;
use App\Models\Bulan;
use App\Models\KategoriKapitasi;
use App\Models\RekapDanaKapitasi;
use App\Models\DanaMasuk;
use App\Models\SisaSaldoKapitasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

class KapitasiController extends Controller
{
    public function index(Request $request)
    {
        $tahun = range(date('Y'), 2000);
        $selectedTahun = $request->tahun ?? null;
        $selectedBulan = $request->bulan ?? null;

        $bulan = Bulan::orderBy('id')->get();
        $kategori = KategoriKapitasi::orderBy('id')->get();

        $grouped = [];
        $annualTotals = [];

        // Inisialisasi annualTotals untuk semua kategori dan total keseluruhan
        foreach ($kategori as $k) {
            $annualTotals[$k->id] = 0;
        }
        $annualTotals['all_kategoris_total'] = 0;

        if ($selectedTahun) {
            $rawData = RekapDanaKapitasi::with(['kategoriKapitasi', 'bulan'])
                ->where('tahun', $selectedTahun)
                ->get();

            foreach ($rawData as $row) {
                $bulanId = $row->bulan_id;
                $kategoriId = $row->kategori_kapitasi_id;

                $totalDanaMasuk = DanaMasuk::where('tahun', $selectedTahun)
                    ->where('bulan_id', $bulanId)
                    ->value('total_dana_masuk');

                if (!isset($grouped[$bulanId])) {
                    $grouped[$bulanId] = [
                        'id' => $row->id,
                        'bulan_id' => $bulanId,
                        'bulan' => $row->bulan->nama,
                        'tahun' => $row->tahun,
                        'validasi' => null,
                        'kategori' => [],
                        'total_biaya_kapitasi' => 0,
                        'total_dana_masuk' => $totalDanaMasuk
                    ];
                }

                // $grouped[$bulanId]['validasi'] = $row->validasi ?? null;

                if ($kategoriId === null) {
                    $grouped[$bulanId]['total_biaya_kapitasi'] = $row->total_biaya_kapitasi;
                    $annualTotals['all_kategoris_total'] += $row->total_biaya_kapitasi;
                } else {
                    $grouped[$bulanId]['kategori'][$kategoriId] = $row->total_biaya_kapitasi;
                    $annualTotals[$kategoriId] += $row->total_biaya_kapitasi;
                }

                // Penting: Setelah loop selesai, urutkan $grouped berdasarkan bulan_id
            }
            ksort($grouped);
            $annualTotals['total_dana_masuk'] = DanaMasuk::where('tahun', $selectedTahun)->sum('total_dana_masuk');
        }

        $saldoAwalTahun = SisaSaldoKapitasi::where('tahun', $selectedTahun)
            ->whereNull('bulan_id')
            ->value('saldo_awal_tahun') ?? 0;

        $sisaSaldoPerBulan = [];
        $previousSaldo = $saldoAwalTahun;
        $saldoSaatIni = 0;

        if ($selectedTahun) {
            foreach ($bulan as $b) {
                $bulanId = $b->id;

                $totalDanaMasuk = DanaMasuk::where('tahun', $selectedTahun)
                    ->where('bulan_id', $bulanId)
                    ->value('total_dana_masuk') ?? 0;

                $totalBiayaKapitasi = RekapDanaKapitasi::where('tahun', $selectedTahun)
                    ->where('bulan_id', $bulanId)
                    ->whereNull('kategori_kapitasi_id')
                    ->value('total_biaya_kapitasi') ?? 0;

                $sisaSaldo = $previousSaldo + $totalDanaMasuk - $totalBiayaKapitasi;

                $sisaSaldoPerBulan[] = [
                    'nama_bulan' => $b->nama,
                    'sisa_saldo' => $sisaSaldo,
                ];

                $previousSaldo = $sisaSaldo;
            }

            $saldoSaatIni = $previousSaldo;
        }
        return view('rekap.kapitasi', compact('bulan', 'tahun', 'selectedTahun', 'selectedBulan', 'kategori', 'grouped', 'annualTotals', 'saldoAwalTahun', 'saldoSaatIni', 'sisaSaldoPerBulan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bulan_id' => 'required|exists:bulans,id',
            'tahun' => 'required|integer|min:2025|max:' . date('Y'),
            'total_biaya_kapitasi' => 'required|array',
            'total_biaya_kapitasi.*' => 'required|numeric',
            'total_dana_masuk' => 'required|numeric|min:0', // Tambahan validasi

        ]);

        $tahun = $request->tahun;
        $bulanId = $request->bulan_id;

        $kategoriCount = KategoriKapitasi::count();
        $existingCount = RekapDanaKapitasi::where('tahun', $tahun)
            ->where('bulan_id', $bulanId)
            ->whereNotNull('kategori_kapitasi_id')
            ->count();

        $existingTotalRecord = RekapDanaKapitasi::where('tahun', $tahun)
            ->where('bulan_id', $bulanId)
            ->whereNull('kategori_kapitasi_id')
            ->exists();

        if ($existingCount >= $kategoriCount && $existingTotalRecord) {
            return redirect()->route('rekap.kapitasi.index', [
                'tahun' => $tahun,
                'bulan_id' => $bulanId,
            ])->with('success', '⚠️ Data input sudah pernah diinputkan, silakan cek tabel kembali.');
        }

        RekapDanaKapitasi::where('tahun', $tahun)
            ->where('bulan_id', $bulanId)
            ->delete();

        $totalBiayaKapitasiBulanIni = 0;

        //Penyimpanan detail biaya
        foreach ($request->input('total_biaya_kapitasi') as $kategoriId => $valueInput) {
            $totalBiayaKapitasiDetail = (int) $valueInput;

            RekapDanaKapitasi::create([
                'kategori_kapitasi_id' => $kategoriId,
                'bulan_id' => $bulanId,
                'tahun' => $tahun,
                'total_biaya_kapitasi' => $totalBiayaKapitasiDetail,
                'validasi' => null,
            ]);
            $totalBiayaKapitasiBulanIni += $totalBiayaKapitasiDetail;
        }
        //Penyimpanan total biaya bulanan
        RekapDanaKapitasi::create([
            'kategori_kapitasi_id' => null,
            'bulan_id' => $bulanId,
            'tahun' => $tahun,
            'total_biaya_kapitasi' => $totalBiayaKapitasiBulanIni,
            'validasi' => null,
        ]);

        DanaMasuk::updateOrCreate(
            [
                'tahun' => $tahun,
                'bulan_id' => $bulanId,
            ],
            [
                'total_dana_masuk' => $request->total_dana_masuk,
            ]
        );

        return redirect()->route('rekap.kapitasi.index', [
            'tahun' => $tahun,
            'bulan_id' => $bulanId,
        ])->with('success', '✅ Data berhasil disimpan!');
    }

    public function update(Request $request, $tahun, $bulan_id)
    {
        $request->validate([
            'total_biaya_kapitasi' => 'required|array',
            'total_biaya_kapitasi.*' => 'required|numeric',
        ]);

        $totalRecord = RekapDanaKapitasi::where('tahun', $tahun)
            ->where('bulan_id', $bulan_id)
            ->whereNull('kategori_kapitasi_id')
            ->first();

        if ($totalRecord && $totalRecord->validasi !== null) {
            return redirect()->route('rekap.kapitasi.index', [
                'tahun' => $tahun,
                'bulan_id' => $bulan_id,
            ])->with('error', '⚠️ Data sudah tervalidasi dan tidak bisa diedit.');
        }

        $totalBiayaKapitasiBulanIni = 0;

        foreach ($request->input('total_biaya_kapitasi') as $kategoriId => $valueInput) {
            $totalBiayaKapitasiDetail = (int) $valueInput;

            RekapDanaKapitasi::updateOrCreate(
                [
                    'kategori_kapitasi_id' => $kategoriId,
                    'bulan_id' => $bulan_id,
                    'tahun' => $tahun,
                ],
                [
                    'total_biaya_kapitasi' => $totalBiayaKapitasiDetail,
                ]
            );
            $totalBiayaKapitasiBulanIni += $totalBiayaKapitasiDetail;
        }

        RekapDanaKapitasi::updateOrCreate(
            [
                'kategori_kapitasi_id' => null,
                'bulan_id' => $bulan_id,
                'tahun' => $tahun,
            ],
            [
                'total_biaya_kapitasi' => $totalBiayaKapitasiBulanIni,
            ]
        );

        DanaMasuk::updateOrCreate(
            [
                'tahun' => $tahun,
                'bulan_id' => $bulan_id,
            ],
            [
                'total_dana_masuk' => $request->total_dana_masuk,
            ]
        );

        return redirect()->route('rekap.kapitasi.index', [
            'tahun' => $tahun,
            'bulan' => $bulan_id,
        ])->with('success', '✅ Data berhasil diperbarui!');
    }

    public function destroy(Request $request, $tahun, $bulan_id)
    {
        $totalRecord = RekapDanaKapitasi::where('tahun', $tahun)
            ->where('bulan_id', $bulan_id)
            ->whereNull('kategori_kapitasi_id')
            ->first();

        if ($totalRecord && $totalRecord->validasi !== null) {
            return redirect()->route('rekap.kapitasi.index', [
                'tahun' => $tahun,
                'bulan' => $bulan_id,
            ])->with('error', '❌ Data sudah tervalidasi dan tidak bisa dihapus.');
        }

        RekapDanaKapitasi::where('tahun', $tahun)
            ->where('bulan_id', $bulan_id)
            ->delete();

        DanaMasuk::where('tahun', $tahun)
            ->where('bulan_id', $bulan_id)
            ->delete();

        return redirect()->route('rekap.kapitasi.index', [
            'tahun' => $tahun,
        ])->with('success', '🗑️ Semua data untuk bulan ini berhasil dihapus.');
    }

    public function validateRekap(Request $request, $tahun, $bulan_id)
    {
        $totalRecord = RekapDanaKapitasi::where('tahun', $tahun)
            ->where('bulan_id', $bulan_id)
            ->whereNull('kategori_kapitasi_id')
            ->firstOrFail();

        $totalRecord->validasi = 'Tervalidasi';
        $totalRecord->save();

        return redirect()->route('rekap.kapitasi.index', [
            'tahun' => $tahun,
            'bulan' => $bulan_id,
        ])->with('success', '✅ Data berhasil divalidasi!');
    }

    public function updateSaldoAwal(Request $request, $tahun)
    {
        $request->merge([
            'saldo_awal_tahun' => str_replace('.', '', $request->saldo_awal_tahun),
        ]);

        $request->validate([
            'saldo_awal_tahun' => 'required|numeric|min:0',
        ]);

        $updatedData = SisaSaldoKapitasi::updateOrCreate(
            ['tahun' => $tahun],
            [
                'bulan_id' => null,
                'saldo_awal_tahun' => $request->saldo_awal_tahun
            ]
        );

        return redirect()
            ->route('rekap.kapitasi.index', ['tahun' => $tahun])
            ->with('success', '✅ Saldo awal tahun berhasil diperbarui.');
    }
}
