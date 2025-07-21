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

        $query = InputManual::with('subkategori')
            ->whereIn('subkategori_id', $subkategoris->pluck('id'))
            ->where('unit_id', Auth::user()->unit_id); // Filter berdasarkan unit login

        if ($request->has('filter') && $request->filter !== null) {
            $query->where('subkategori_id', $request->filter);
        }

        $data = $query->get();

        return view('laporan.kategori-khusus', compact('data', 'subkategoris'));
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

        // 1. Simpan data ke input_manual
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

        // 2. Simpan juga ke laporan_bulanan
        \App\Models\LaporanBulanan::create([
            'kategori_id' => 21, // kategori khusus
            'subkategori_id' => $request->subkategori_id,
            'unit_id' => $unitId,
            'user_id' => $userId,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'jumlah' => 1, // atau bisa hitung otomatis jumlah pekerja jika batch
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

    // public function destroy($id)
    // {
    //     $item = InputManual::findOrFail($id);
    //     $item->delete();

    //     return redirect()->route('laporan.kategori-khusus.index')->with('success', 'Data berhasil dihapus.');
    // }
}
