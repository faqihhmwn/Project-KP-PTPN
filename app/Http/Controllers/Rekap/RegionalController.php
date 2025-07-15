<?php

namespace App\Http\Controllers\Rekap;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\Bulan;
use App\Models\KategoriBiaya;
use App\Models\RekapBiayaKesehatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class RegionalController extends Controller
{
    public function index(Request $request)
    {
        $tahun = range(date('Y'), 2000);
        $selectedTahun = $request->tahun ?? null;

        $bulan = Bulan::orderBy('id')->get();
        $kategori = KategoriBiaya::orderBy('id')->get();
        $selectedBulan = null;

        $grouped = [];

        if ($selectedTahun) {
            $rawData = RekapBiayaKesehatan::with(['kategoriBiaya', 'bulan'])
                ->where('tahun', $selectedTahun)
                ->get();
        foreach ($rawData as $row) {
            $bulanId = $row->bulan_id;
            $kategoriId = $row->kategori_biaya_id;

            if (!isset($grouped[$bulanId]['id'])) {
                $grouped[$bulanId]['id'] = $row->id;
            }

            $grouped[$bulanId]['bulan'] = $row->bulan->nama;
            $grouped[$bulanId]['tahun'] = $row->tahun;
            $grouped[$bulanId]['kategori'][$kategoriId] = $row->jumlah;
            $grouped[$bulanId]['validasi'] = $row->validasi ?? null;
        }
    }
    return view('rekap.regional', compact('bulan', 'tahun', 'selectedTahun', 'selectedBulan', 'kategori', 'grouped'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bulan_id' => 'required|exists:bulans,id', // gunakan bulan_id dari tabel bulans
            'tahun' => 'required|integer|min:2000|max:' . date('Y'),
            'jumlah' => 'required|array',
            'jumlah.*' => 'required|numeric|min:0', 
        ]);

         // Cek apakah data sudah pernah diinputkan
        $existing = RekapBiayaKesehatan::where('tahun', $request->tahun)
            ->where('bulan_id', $request->bulan_id)
            ->exists();

        if ($existing) {
            return redirect()->route('rekap.regional.index', [
                'tahun' => $request->tahun,
                'bulan' => $request->bulan_id,
            ])->with('success', '⚠️ Data input sudah pernah diinputkan, silakan cek tabel kembali.');
        }

        //simpan data baru per kategori
        foreach ($request->input('jumlah') as $kategoriId => $jumlah) {
            // Bersihkan input rupiah, buang titik
            $jumlahBersih = (int) str_replace(['.', ','], '', $jumlah);

        RekapBiayaKesehatan::create([
                    'kategori_biaya_id' => $kategoriId,
                    'bulan_id' => $request->bulan_id,
                    'tahun' => $request->tahun,
                    'jumlah' => $jumlahBersih,
                    'validasi' => null,
                ]);
            }
            return redirect()->route('rekap.regional.index', [
            'tahun' => $request->tahun,
            'bulan' => $request->bulan_id,
        ])->with('success', 'Data berhasil disimpan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
        'jumlah' => 'required|array',
        'jumlah.*' => 'required|numeric|min:0',
    ]);

        $rekap = RekapBiayaKesehatan::findOrFail($id);

        // Cegah update jika data sudah tervalidasi
        if ($rekap->validasi !== null) {
            return redirect()->route('rekap.regional.index', [
                'tahun' => $rekap->tahun,
                'bulan' => $rekap->bulan_id,
            ])->with('success', '⚠️ Data sudah tervalidasi dan tidak bisa diedit.');
        }

        foreach ($request->input('jumlah') as $kategoriId => $jumlah) {
            $jumlahBersih = (int) str_replace(['.', ','], '', $jumlah); // format rupiah

            RekapBiayaKesehatan::updateOrCreate(
                [
                    'kategori_biaya_id' => $kategoriId,
                    'bulan_id' => $rekap->bulan_id,
                    'tahun' => $rekap->tahun,
                ],
                [
                    'jumlah' => $jumlahBersih,
                ]
            );
        }

        return redirect()->route('rekap.regional.index', [
            'tahun' => $rekap->tahun,
            'bulan' => $rekap->bulan_id,
        ])->with('success', '✅ Data berhasil diperbarui!');
    }




    public function destroy($id)
    {
        $item = RekapBiayaKesehatan::findOrFail($id);
        $item->delete();

        return redirect()->route('rekap.regional.index')->with('success', 'Data berhasil dihapus.');
    }

    // RekapRegionalController.php
    public function validateRekap($id)
    {
        $rekap = RekapBiayaKesehatan::findOrFail($id);
        $rekap->validasi = 'Tervalidasi'; // atau true
        $rekap->save();

        return redirect()->route('rekap.regional.index')->with('success', 'Data berhasil divalidasi.');
    }



}