<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\InputManual;
use App\Models\SubKategori;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KategoriKhususController extends Controller
{
    const KATEGORI_ID = 21;

    public function index(Request $request)
    {
        $is_admin = Auth::guard('admin')->check();
        $authUser = Auth::guard('admin')->user() ?? Auth::guard('web')->user();

        $subkategoris = SubKategori::where('kategori_id', self::KATEGORI_ID)->get();
        $units = Unit::all();

        $query = InputManual::with(['subkategori', 'unit'])
            ->where('kategori_id', self::KATEGORI_ID);

        // Filter
        $unitId = $request->input('unit_id');
        $status = $request->input('status');
        $searchName = $request->input('search_name');

        if ($is_admin) {
            if ($unitId) $query->where('unit_id', $unitId);
        } else {
            $query->where('unit_id', $authUser->unit_id);
        }

        if ($status) $query->where('status', $status);
        if ($searchName) $query->where('nama', 'like', '%' . $searchName . '%');
        
        $data = $query->orderBy('created_at', 'desc')
                      ->paginate(10)
                      ->appends($request->query());

        $viewData = compact('data', 'subkategoris', 'units', 'unitId', 'status', 'searchName');

        if ($request->ajax()) {
            if ($is_admin) {
                return view('admin.laporan.partials.kategori-khusus_admin_content', $viewData)->render();
            }
            // Partial view untuk user bisa ditambahkan di sini jika perlu
        }
        
        if ($is_admin) {
            return view('admin.laporan.kategori-khusus', $viewData);
        } else {
            return view('laporan.kategori-khusus', $viewData);
        }
    }

    public function store(Request $request)
    {
        $is_admin = Auth::guard('admin')->check();
        $rules = [
            'subkategori_id' => 'required|exists:subkategori,id',
            'nama' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'jenis_disabilitas' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:255',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:' . date('Y'),
        ];
        if ($is_admin) {
            $rules['unit_id'] = 'required|exists:units,id';
        }
        $request->validate($rules);
        
        $authUser = Auth::guard('admin')->user() ?? Auth::guard('web')->user();
        $unitId = $is_admin ? $request->unit_id : $authUser->unit_id;
        $userId = $is_admin ? null : $authUser->id;
        
        InputManual::create([
            'kategori_id' => self::KATEGORI_ID,
            'subkategori_id' => $request->subkategori_id,
            'unit_id' => $unitId,
            'user_id' => $userId,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'nama' => $request->nama,
            'status' => $request->status,
            'jenis_disabilitas' => $request->jenis_disabilitas,
            'keterangan' => $request->keterangan,
        ]);
        
        return back()->with('success', 'Data berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $item = InputManual::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'jenis_disabilitas' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:500',
        ]);
        
        $item->update($request->only(['nama', 'status', 'jenis_disabilitas', 'keterangan']));
        
        return back()->with('success', 'Data berhasil diperbarui.');
    }
    
    public function destroy($id)
    {
        if (!Auth::guard('admin')->check()) {
            return back()->with('error', 'Anda tidak memiliki izin untuk menghapus data.');
        }

        $item = InputManual::findOrFail($id);
        $item->delete();

        return back()->with('success', 'Data berhasil dihapus.');
    }
}