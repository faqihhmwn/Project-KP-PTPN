<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\LaporanBulanan;
use App\Models\SubKategori;
use App\Models\Unit;
use App\Models\LaporanApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exports\LaporanOpnameExport;
use Maatwebsite\Excel\Facades\Excel;

class OpnameController extends Controller
{
    const KATEGORI_ID = 3; // ID untuk kategori Opname

    public function index(Request $request)
    {
        $is_admin = Auth::guard('admin')->check();
        $authUser = Auth::guard('admin')->user() ?? Auth::guard('web')->user();

        $subkategori = SubKategori::where('kategori_id', self::KATEGORI_ID)->get();
        $units = Unit::all();

        $existingData = LaporanBulanan::where('kategori_id', self::KATEGORI_ID)
            ->select('unit_id', 'bulan', 'tahun')
            ->distinct()
            ->get()
            ->keyBy(function ($item) {
                return $item->unit_id . '-' . $item->bulan . '-' . $item->tahun;
            });

        $query = LaporanBulanan::with(['subkategori', 'unit'])
            ->where('kategori_id', self::KATEGORI_ID);

        // Filter
        $unitId = $request->input('unit_id');
        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun');
        $subkategoriId = $request->input('subkategori_id');

        if ($is_admin) {
            if ($unitId) $query->where('unit_id', $unitId);
        } else {
            $query->where('unit_id', $authUser->unit_id);
        }

        if ($subkategoriId) $query->where('subkategori_id', $subkategoriId);
        if ($bulan) $query->where('bulan', $bulan);
        if ($tahun) $query->where('tahun', $tahun);

        $data = $query->orderBy('tahun', 'desc')
                      ->orderByRaw("CAST(bulan AS UNSIGNED) DESC")
                      ->orderBy('subkategori_id', 'asc')
                      ->paginate(10)
                      ->appends($request->query());
            
        $approvals = LaporanApproval::where('kategori_id', self::KATEGORI_ID)->get()->keyBy(function ($item) {
            return $item->unit_id . '-' . $item->bulan . '-' . $item->tahun;
        });

        $viewData = compact('data', 'subkategori', 'units', 'unitId', 'bulan', 'tahun', 'approvals', 'subkategoriId', 'authUser', 'existingData');

        if ($request->ajax()) {
            if ($is_admin) {
                return view('admin.laporan.partials.opname_admin_content', $viewData)->render();
            } else {
                return view('laporan.partials.opname_table', $viewData)->render();
            }
        }

        if ($is_admin) {
            return view('admin.laporan.opname', $viewData);
        } else {
            return view('laporan.opname', $viewData);
        }
    }

    public function store(Request $request)
    {
        $is_admin = Auth::guard('admin')->check();

        $rules = [
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:' . date('Y'),
            'jumlah' => 'present|array',
            'jumlah.*' => 'required|numeric|min:0' // Diubah menjadi 'required'
        ];

        if ($is_admin) {
            $rules['unit_id'] = 'required|exists:units,id';
        }
        $request->validate($rules);

        $authUser = Auth::guard('admin')->user() ?? Auth::guard('web')->user();
        $unitId = $is_admin ? $request->unit_id : $authUser->unit_id;
        $userId = $is_admin ? null : $authUser->id;

        $isApproved = LaporanApproval::where('unit_id', $unitId)
            ->where('kategori_id', self::KATEGORI_ID)
            ->where('bulan', $request->bulan)
            ->where('tahun', $request->tahun)
            ->exists();

        if ($isApproved && !$is_admin) {
            return back()->with('error', 'Data untuk periode ini sudah disetujui dan tidak dapat diubah.');
        }
        
        $existingData = LaporanBulanan::where('unit_id', $unitId)
            ->where('kategori_id', self::KATEGORI_ID)
            ->where('bulan', $request->bulan)
            ->where('tahun', $request->tahun)
            ->exists();

        if ($existingData) {
            return back()->with('error', 'Data untuk periode ini sudah ada. Silahkan gunakan fitur "Edit" untuk mengubahnya.');
        }
        
        foreach ($request->input('jumlah') as $subkategori_id => $jumlah) {
            LaporanBulanan::create([
                'unit_id' => $unitId,
                'kategori_id' => self::KATEGORI_ID,
                'subkategori_id' => $subkategori_id,
                'bulan' => $request->bulan,
                'tahun' => $request->tahun,
                'user_id' => $userId,
                'jumlah' => $jumlah ?? 0,
            ]);
        }

        return back()->with('success', 'Laporan berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $laporan = LaporanBulanan::findOrFail($id);
        
        $isApproved = LaporanApproval::where('unit_id', $laporan->unit_id)
            ->where('kategori_id', $laporan->kategori_id)
            ->where('bulan', $laporan->bulan)
            ->where('tahun', $laporan->tahun)
            ->exists();

        if ($isApproved && !Auth::guard('admin')->check()) {
            return redirect()->back()->with('error', 'Data untuk periode ini sudah disetujui dan tidak dapat diubah.');
        }

        $request->validate(['jumlah' => 'required|numeric|min:0']);
        $laporan->update($request->only(['jumlah']));
        
        return redirect()->back()->with('success', 'Laporan berhasil diperbarui.');
    }
    
    // public function destroy($id)
    // {
        // if (!Auth::guard('admin')->check()) {
            // return back()->with('error', 'Anda tidak memiliki izin untuk menghapus data.');
        // }

        // $laporan = LaporanBulanan::findOrFail($id);
        // $laporan->delete();

        // return back()->with('success', 'Data berhasil dihapus.');
    // }

    public function approve(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return back()->with('error', 'Hanya admin yang dapat menyetujui laporan.');
        }

        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'bulan' => 'required|numeric|between:1,12',
            'tahun' => 'required|numeric|min:2020',
        ]);

        LaporanApproval::updateOrCreate(
            ['unit_id' => $request->unit_id, 'kategori_id' => self::KATEGORI_ID, 'bulan' => $request->bulan, 'tahun' => $request->tahun],
            ['approved_by' => Auth::guard('admin')->id(), 'approved_at' => now()]
        );

        return back()->with('success', 'Data periode berhasil disetujui dan dikunci.');
    }

    public function unapprove(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return back()->with('error', 'Hanya admin yang dapat membatalkan persetujuan.');
        }

        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'bulan' => 'required|numeric|between:1,12',
            'tahun' => 'required|numeric|min:2020',
        ]);

        $approval = LaporanApproval::where([
            'unit_id' => $request->unit_id,
            'kategori_id' => self::KATEGORI_ID,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
        ])->first();

        if ($approval) {
            $approval->delete();
            return back()->with('success', 'Persetujuan dibatalkan. Periode ini sekarang bisa diubah.');
        }

        return back()->with('error', 'Data persetujuan tidak ditemukan.');
    }

    public function export(Request $request)
    {
        $unitId = $request->input('unit_id');
        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun');
        $subkategoriId = $request->input('subkategori_id');

        // Jika yang login adalah user biasa (bukan admin),
        // paksa filter unit_id menjadi unit milik user tersebut.
        if (Auth::guard('web')->check()) {
            $unitId = Auth::guard('web')->user()->unit_id;
        }

        $fileName = 'laporan_opname_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Panggil class export yang baru dengan parameter filter
        return Excel::download(new LaporanOpnameExport($unitId, $bulan, $tahun, $subkategoriId), $fileName);
    }
}