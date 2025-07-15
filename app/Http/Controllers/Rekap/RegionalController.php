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
        $selectedTahun = request('tahun') ?? date('Y'); // default ke tahun ini 
        $bulan = Bulan::orderBy('id')->get(); // Ambil semua bulan

        $selectedBulan = $request->bulan;

        if ($selectedBulan) {
            $rawData = RekapBiayaKesehatan::with(['kategoriBiaya', 'bulan'])
                ->where('tahun', $selectedTahun)
                ->where('bulan_id', $selectedBulan)
                ->get();
        } else {
            $rawData = collect(); // kosong
        }

        $kategori = KategoriBiaya::orderBy('id')->get(); // Ambil semua kategori
        $data = RekapBiayaKesehatan::with(['kategoriBiaya', 'bulan']) // atau ->with('subkategori') jika kamu pakai subkategori
            ->where('tahun', $selectedTahun)
            ->orderBy('bulan_id')
            ->orderBy('kategori_biaya_id')
            ->get();
        
        // Ambil semua data rekap untuk tahun terpilih
        $rawData = RekapBiayaKesehatan::with(['kategoriBiaya', 'bulan'])
        ->where('tahun', $selectedTahun)
        ->get();

        // Transform ke format: [$bulan_id][$kategori_id] = jumlah
        // // $grouped = [];
        // foreach ($rawData as $row) {
        //     $bulanId = $row->bulan_id;
        //     $kategoriId = $row->kategori_biaya_id;

        //     //menyimpan 1 i per kombinasi bulan
        //     $grouped[$bulanId]['bulan'] = $row->bulan->nama;
        //     $grouped[$bulanId]['tahun'] = $row->tahun;
        //     $grouped[$bulanId]['kategori'][$kategoriId] = $row->jumlah;
        //     $grouped[$bulanId]['validasi'] = $row->validasi ?? null;
        // }

        $grouped = [];

        foreach ($rawData as $row) {
            $bulanId = $row->bulan_id;
            $kategoriId = $row->kategori_biaya_id;

            // ✅ Simpan hanya satu ID per bulan, gunakan ID dari salah satu entri
            if (!isset($grouped[$bulanId]['id'])) {
                $grouped[$bulanId]['id'] = $row->id;
            }

            $grouped[$bulanId]['bulan'] = $row->bulan->nama;
            $grouped[$bulanId]['tahun'] = $row->tahun;
            $grouped[$bulanId]['kategori'][$kategoriId] = $row->jumlah;
            $grouped[$bulanId]['validasi'] = $row->validasi ?? null;
        }



    return view('rekap.regional', compact('bulan', 'tahun', 'selectedTahun', 'selectedBulan', 'kategori', 'grouped', 'data'));

    }

    public function store(Request $request)
{
    
    $request->validate([
        'bulan_id' => 'required|exists:bulans,id', // gunakan bulan_id dari tabel bulans
        'tahun' => 'required|integer|min:2000|max:' . date('Y'),
        'jumlah' => 'required|array',
        'jumlah.*' => 'required|string', // karena input berupa string (rupiah dengan titik)
    ]);

    foreach ($request->input('jumlah') as $kategori => $jumlah) {
        // Bersihkan input rupiah, buang titik
        $jumlahBersih = (int) str_replace(['.', ','], '', $jumlah);

        RekapBiayaKesehatan::updateOrCreate(
            [
                'kategori_biaya_id' => $kategori, // disesuaikan dengan kategori tertentu
                'bulan_id' => $request->bulan, // ini adalah bulan_id
                'tahun' => $request->tahun,
            ],
            [
                'jumlah' => $jumlahBersih,
            ]
        );
    }

    return redirect()->route('rekap.regional.index')
        ->with('success', 'Laporan berhasil ditambahkan');
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