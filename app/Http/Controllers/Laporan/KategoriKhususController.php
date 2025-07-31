<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\InputManual;
use App\Models\SubKategori;
use App\Models\Unit;
use App\Models\LaporanApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Exports\LaporanKategoriKhususExport;
use Maatwebsite\Excel\Facades\Excel;

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
            ->whereHas('subkategori', function ($q) {
                $q->where('kategori_id', self::KATEGORI_ID);
            });

        // Filter
        $unitId = $request->input('unit_id');
        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun');
        $subkategoriId = $request->input('subkategori_id');
        $status = $request->input('status');
        $searchName = $request->input('search_name');
        $jenisDisabilitas = $request->input('jenis_disabilitas');

        if ($is_admin) {
            if ($unitId) $query->where('unit_id', $unitId);
        } else {
            $query->where('unit_id', $authUser->unit_id);
        }

        if ($subkategoriId) $query->where('subkategori_id', $subkategoriId);
        if ($status) $query->where('status', $status);
        if ($searchName) $query->where('nama', 'like', '%' . $searchName . '%');
        if ($jenisDisabilitas) $query->where('jenis_disabilitas', $jenisDisabilitas);
        if ($bulan) $query->where('bulan', $bulan);
        if ($tahun) $query->where('tahun', $tahun);
        
        $data = $query->orderBy('created_at', 'desc')
                      ->paginate(10)
                      ->appends($request->query());

        $approvals = LaporanApproval::where('kategori_id', self::KATEGORI_ID)->get()->keyBy(function ($item) {
            return $item->unit_id . '-' . $item->bulan . '-' . $item->tahun;
        });

        $viewData = compact('data', 'subkategoris', 'units', 'unitId', 'bulan', 'tahun', 'subkategoriId', 'status', 'searchName', 'authUser', 'jenisDisabilitas', 'approvals');

        if ($request->ajax()) {
            if ($is_admin) {
                return view('admin.laporan.partials.kategori-khusus_admin_content', $viewData)->render();
            } else {
                return view('laporan.partials.kategori-khusus_table', $viewData)->render();
            }
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
            'jenis_disabilitas' => [
                Rule::requiredIf($request->subkategori_id == 82),
                'nullable',
                'string',
                'max:255'
            ],
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

        $isApproved = LaporanApproval::where('unit_id', $unitId)
            ->where('kategori_id', self::KATEGORI_ID)
            ->where('bulan', $request->bulan)
            ->where('tahun', $request->tahun)
            ->exists();

        if ($isApproved && !$is_admin) {
            return back()->with('error', 'Data untuk periode ini sudah disetujui dan tidak dapat diubah.');
        }
        
        InputManual::create([
            'subkategori_id' => $request->subkategori_id,
            'unit_id' => $unitId,
            'user_id' => $userId,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'nama' => $request->nama,
            'status' => $request->status,
            'jenis_disabilitas' => $request->subkategori_id == 82 ? $request->jenis_disabilitas : null,
            'keterangan' => $request->keterangan,
        ]);
        
        return back()->with('success', 'Data berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $item = InputManual::findOrFail($id);
        $is_admin = Auth::guard('admin')->check();

        // Validasi 1: Periode ASAL data tidak boleh sudah diapprove (untuk user)
        $isCurrentPeriodApproved = LaporanApproval::where('unit_id', $item->unit_id)
            ->where('kategori_id', self::KATEGORI_ID)
            ->where('bulan', $item->bulan)
            ->where('tahun', $item->tahun)
            ->exists();

        if ($isCurrentPeriodApproved && !$is_admin) {
            return back()->with('error', 'Data untuk periode ini sudah disetujui dan tidak dapat diubah.');
        }

        // Aturan validasi dasar
        $rules = [
            'nama' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'jenis_disabilitas' => [
                Rule::requiredIf($request->subkategori_id == 82),
                'nullable',
                'string',
                'max:255'
            ],
            'keterangan' => 'nullable|string|max:500',
        ];

        // Tambahkan aturan validasi periode jika user yang mengedit
        if (!$is_admin) {
            $rules['bulan'] = 'required|integer|min:1|max:12';
            $rules['tahun'] = 'required|integer|min:2020|max:' . date('Y');
        } else {
            // Admin juga bisa mengubah unit
            $rules['unit_id'] = 'required|exists:units,id';
        }
        
        $request->validate($rules);

        // Validasi approval untuk periode baru (hanya untuk user)
        if (!$is_admin) {
            $isNewPeriodApproved = LaporanApproval::where('unit_id', $item->unit_id)
                ->where('kategori_id', self::KATEGORI_ID)
                ->where('bulan', $request->bulan)
                ->where('tahun', $request->tahun)
                ->exists();
            if ($isNewPeriodApproved) {
                return back()->with('error', 'Periode tujuan sudah disetujui dan data tidak bisa dipindahkan.');
            }
        }
        // Tentukan data yang akan diupdate
        $updateData = $request->only(['nama', 'status', 'jenis_disabilitas', 'keterangan']);
        if($is_admin) {
            $updateData = array_merge($updateData, $request->only(['unit_id', 'bulan', 'tahun', 'subkategori_id']));
        } else {
             $updateData = array_merge($updateData, $request->only(['bulan', 'tahun', 'subkategori_id']));
        }

        $item->update($updateData);
        
        return back()->with('success', 'Data berhasil diperbarui.');
    }
    
    public function destroy($id)
    {
        $item = InputManual::findOrFail($id);

        $isApproved = LaporanApproval::where('unit_id', $item->unit_id)
            ->where('kategori_id', self::KATEGORI_ID)
            ->where('bulan', $item->bulan)
            ->where('tahun', $item->tahun)
            ->exists();

        if ($isApproved && !Auth::guard('admin')->check()) {
            return back()->with('error', 'Data untuk periode ini sudah disetujui dan tidak dapat dihapus.');
        }

        if (!Auth::guard('admin')->check()) {
            return back()->with('error', 'Anda tidak memiliki izin untuk menghapus data.');
        }

        $item->delete();

        return back()->with('success', 'Data berhasil dihapus.');
    }

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

        if (Auth::guard('web')->check()) {
            $unitId = Auth::guard('web')->user()->unit_id;
        }

        $fileName = 'laporan_kategori_khusus_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new LaporanKategoriKhususExport($unitId, $bulan, $tahun, $subkategoriId), $fileName);
    }
}