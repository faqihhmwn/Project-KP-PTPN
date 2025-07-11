<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\LaporanBulanan;
use App\Models\SubKategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KonsultasiKlinikController extends Controller
{
   public function index()
{
    $data = LaporanBulanan::with(['subkategori', 'unit'])
        ->where('kategori_id', 5)
        ->where('unit_id', Auth::user()->unit_id)
        ->get();

    $subkategori = SubKategori::where('kategori_id', 5)->get();

    return view('laporan.konsultasi-klinik', compact('data', 'subkategori'));
}

public function create()
{
    $subkategoris = SubKategori::where('kategori_id', 5)->get();
    return view('laporan.konsultasi-klinik', compact('subkategoris'));
}

    public function store(Request $request)
{
    $request->validate([
        'bulan' => 'required|integer|min:1|max:12',
        'tahun' => 'required|integer|min:2000|max:' . date('Y'),
        'jumlah' => 'required|array',
    ]);

        foreach ($request->input('jumlah') as $subkategori_id => $jumlah) {
            LaporanBulanan::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'unit_id' => Auth::user()->unit_id,
                    'kategori_id' => 5,
                    'subkategori_id' => $subkategori_id,
                    'bulan' => $request->bulan,
                    'tahun' => $request->tahun,
                ],
                [
                    'jumlah' => $jumlah,
                ]
            );
        }

        return redirect()->route('laporan.konsultasi-klinik.index')->with('success', 'Laporan berhasil ditambahkan');
    }

        public function edit($id)
    {
        $laporan = LaporanBulanan::findOrFail($id);
        $subkategoris = SubKategori::where('kategori_id', 5)->get();
        return view('laporan.konsultasi-klinik', compact('laporan', 'subkategoris'));
    }

    public function update(Request $request, $id)
    {
        $laporan = LaporanBulanan::findOrFail($id);
        $laporan->update([
            'jumlah' => $request->jumlah,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'subkategori_id' => $request->subkategori_id,
        ]);

        return redirect()->route('laporan.konsultasi-klinik.index')->with('success', 'Laporan berhasil diperbarui');
    }

    public function destroy($id)
    {
        $laporan = LaporanBulanan::findOrFail($id);
        $laporan->delete();

        return redirect()->route('laporan.konsultasi-klinik.index')->with('success', 'Laporan berhasil dihapus');
    }


}
