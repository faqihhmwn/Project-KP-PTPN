<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\LaporanBulanan;
use App\Models\SubKategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CutiSakitController extends Controller
{
    public function index(Request $request)
    {
        $subkategori = SubKategori::where('kategori_id', 6)->get();
        $is_admin = Auth::guard('admin')->check();
        $authUser = Auth::guard('admin')->user() ?? Auth::guard('web')->user();

        $query = LaporanBulanan::with(['subkategori', 'unit'])
            ->where('kategori_id', 6);
        
        if (!$is_admin) {
            $query->where('unit_id', $authUser->unit_id);
        }

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
        
        if ($is_admin) {
            return view('admin.laporan.cuti-sakit', compact('data', 'subkategori'));
        } else {
            return view('laporan.cuti-sakit', compact('data', 'subkategori'));
        }
    }

    public function create()
    {
        $subkategoris = SubKategori::where('kategori_id', 6)->get();
        return view('laporan.cuti-sakit', compact('subkategoris'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:' . date('Y'),
            'jumlah' => 'required|array',
        ]);
        foreach ($request->input('jumlah') as $subkategori_id => $jumlah) {
            $laporan = LaporanBulanan::where([
                'user_id' => Auth::id(),
                'unit_id' => Auth::user()->unit_id,
                'kategori_id' => 6,
                'subkategori_id' => $subkategori_id,
                'bulan' => $request->bulan,
                'tahun' => $request->tahun,
            ])->first();
            if (!$laporan && $jumlah !== null) {
                LaporanBulanan::create([
                    'user_id' => Auth::id(),
                    'unit_id' => Auth::user()->unit_id,
                    'kategori_id' => 6,
                    'subkategori_id' => $subkategori_id,
                    'bulan' => $request->bulan,
                    'tahun' => $request->tahun,
                    'jumlah' => $jumlah,
                ]);
            } elseif ($laporan && $jumlah != 0) {
                $laporan->update(['jumlah' => $jumlah]);
            }
        }
        return redirect()->route('laporan.cuti-sakit.index')->with('success', 'Laporan berhasil ditambahkan');
    }

    public function edit($id)
    {
        $laporan = LaporanBulanan::findOrFail($id);
        $subkategoris = SubKategori::where('kategori_id', 6)->get();
        return view('laporan.cuti-sakit', compact('laporan', 'subkategoris'));
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
        return redirect()->route('laporan.cuti-sakit.index')->with('success', 'Laporan berhasil diperbarui');
    }
}