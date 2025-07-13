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
    private function parseRupiah($value)
    {
        return (int)str_replace('.', '', $value);
    }
    public function index(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));

        $data = RekapBiayaKesehatan::where('tahun', $tahun)
            ->orderBy('bulan_id')
            ->get()
            ->groupBy('bulan_id');

        $bulans = Bulan::all();
        $tahunList = range(date('Y'), 2000);

        return view('rekap.regional', compact('data', 'bulans', 'tahun', 'tahunList'));
    }

    public function store(Request $request)
    {
        $request->validate([
        'bulan_id' => 'required|exists:bulans,id',
        'tahun' => 'required|integer',
        'kategori_biaya' => 'required|array',
    ]);

    $rekap = RekapBiayaKesehatan::create([
        'bulan_id' => $request->bulan_id,
        'tahun' => $request->tahun,
    ]);

    $total = 0;

    foreach ($request->kategori_biaya as $kategori_biaya_id => $nilai) {
        $rupiah = $this->parseRupiah($nilai);
        $total += $rupiah;

        RekapBiayaKesehatan::create([
            'rekap_id' => $rekap->id,
            'kategori_biaya_id' => $kategori_biaya_id,
            'nilai' => $rupiah,
        ]);
    }

    $rekap->update(['total_biaya_kesehatan' => $total]);

    return redirect()->back()->with('success', 'Data berhasil disimpan');
}
}