<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\LaporanBulanan;
use App\Models\SubKategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OpnameController extends Controller
{
    public function index(Request $request)
    {
        $subkategori = SubKategori::where('kategori_id', 3)->get();

        $query = LaporanBulanan::with(['subkategori', 'unit'])
            ->where('kategori_id', 3)
            ->where('unit_id', Auth::user()->unit_id);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('subkategori', function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%');
            });
        }

        // Filter bulan
        if ($request->filled('bulan')) {
            $query->where('bulan', $request->bulan);
        }

        // Filter tahun
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        $data = $query
            ->orderBy('tahun', 'desc')
            ->orderByRaw("CAST(bulan AS UNSIGNED) DESC")
            ->orderBy(SubKategori::select('nama')
                ->whereColumn('subkategori.id', 'laporan_bulanan.subkategori_id'))
            ->paginate(8);

        return view('laporan.opname', compact('data', 'subkategori'));
    }

    public function create()
    {
        $subkategoris = SubKategori::where('kategori_id', 3)->get();
        return view('laporan.opname', compact('subkategoris'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:' . date('Y'),
            'jumlah' => 'required|array',
        ]);

        $unit_id = Auth::user()->unit_id;
        $user_id = Auth::id();
        $kategori_id = 3;
        $duplicated = [];

        foreach ($request->input('jumlah') as $subkategori_id => $jumlah) {
            // Cek apakah kombinasi ini sudah ada
            $exists = LaporanBulanan::where([
                'user_id' => $user_id,
                'unit_id' => $unit_id,
                'kategori_id' => $kategori_id,
                'subkategori_id' => $subkategori_id,
                'bulan' => $request->bulan,
                'tahun' => $request->tahun,
            ])->exists();

            if ($exists) {
                $duplicated[] = $subkategori_id;
                continue; // Skip simpan duplikat
            }

            if ($jumlah !== null && $jumlah != 0) {
                LaporanBulanan::create([
                    'user_id' => $user_id,
                    'unit_id' => $unit_id,
                    'kategori_id' => $kategori_id,
                    'subkategori_id' => $subkategori_id,
                    'bulan' => $request->bulan,
                    'tahun' => $request->tahun,
                    'jumlah' => $jumlah,
                ]);
            }
        }

        // Handle feedback
        if (!empty($duplicated)) {
            return redirect()->route('laporan.opname.index')
                ->with('warning', 'Beberapa data tidak disimpan karena sudah pernah diinputkan.');
        }

        return redirect()->route('laporan.opname.index')
            ->with('success', 'Laporan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $laporan = LaporanBulanan::findOrFail($id);
        $subkategoris = SubKategori::where('kategori_id', 3)->get();
        return view('laporan.opname', compact('laporan', 'subkategoris'));
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

        return redirect()->route('laporan.opname.index')->with('success', 'Laporan berhasil diperbarui');
    }

    // public function destroy($id)
    // {
    //     $laporan = LaporanBulanan::findOrFail($id);
    //     $laporan->delete();

    //     return redirect()->route('laporan.opname.index')->with('success', 'Laporan berhasil dihapus');
    // }

}
