<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InputManual;
use App\Models\SubKategori;
use App\Models\LaporanBulanan;
use Illuminate\Support\Facades\Auth;

class KategoriKhususController extends Controller
{
    public function index(Request $request)
    {
        $subkategoris = SubKategori::where('kategori_id', 21)->get();
        $is_admin = Auth::guard('admin')->check();
        $authUser = Auth::guard('admin')->user() ?? Auth::guard('web')->user();

        $query = InputManual::with('subkategori')
            ->whereIn('subkategori_id', $subkategoris->pluck('id'));

        if (!$is_admin) {
            $query->where('unit_id', $authUser->unit_id);
        }

        if ($request->has('filter') && $request->filter !== null) {
            $query->where('subkategori_id', $request->filter);
        }

        $data = $query->get();

        if ($is_admin) {
            return view('admin.laporan.kategori-khusus', compact('data', 'subkategoris'));
        } else {
            return view('laporan.kategori-khusus', compact('data', 'subkategoris'));
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'subkategori_id' => 'required|exists:subkategori,id',
            'nama' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'jenis_disabilitas' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:255',
        ]);
        
        $unitId = Auth::user()->unit_id;
        $userId = Auth::id();
        $bulan = now()->month;
        $tahun = now()->year;
        
        $inputManual = InputManual::create([
            'nama' => $request->nama,
            'status' => $request->status,
            'subkategori_id' => $request->subkategori_id,
            'jenis_disabilitas' => $request->jenis_disabilitas,
            'keterangan' => $request->keterangan,
            'unit_id' => $unitId,
            'user_id' => $userId,
            'bulan' => $bulan,
            'tahun' => $tahun,
        ]);
        
        \App\Models\LaporanBulanan::create([
            'kategori_id' => 21,
            'subkategori_id' => $request->subkategori_id,
            'unit_id' => $unitId,
            'user_id' => $userId,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'jumlah' => 1,
            'input_manual_id' => $inputManual->id,
        ]);
        
        return redirect()->route('laporan.kategori-khusus.index')->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $editItem = InputManual::findOrFail($id);
        $subkategoris = SubKategori::where('kategori_id', 21)->get();
        $data = InputManual::with('subkategori')->whereIn('subkategori_id', $subkategoris->pluck('id'))->get();

        return view('laporan.kategori-khusus', compact('data', 'subkategoris', 'editItem'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'subkategori_id' => 'required|exists:subkategori,id',
            'nama' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'jenis_disabilitas' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:500',
        ]);
        
        $item = InputManual::findOrFail($id);
        $item->update([
            'subkategori_id' => $request->subkategori_id,
            'nama' => $request->nama,
            'status' => $request->status,
            'jenis_disabilitas' => $request->jenis_disabilitas,
            'keterangan' => $request->keterangan,
        ]);
        
        return redirect()->route('laporan.kategori-khusus.index')->with('success', 'Data berhasil diperbarui.');
    }
}