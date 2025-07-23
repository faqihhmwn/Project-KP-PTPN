<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LaporanBulanan;
use App\Models\Unit;
use App\Models\Obat;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'laporan');

        $kategoriList = [
            1 => 'Kependudukan',
            2 => 'Penyakit',
            3 => 'Opname',
            4 => 'Penyakit Kronis',
            5 => 'Konsultasi Klinik',
            6 => 'Cuti Sakit',
            7 => 'Peserta KB',
            8 => 'Metode KB',
            9 => 'Kehamilan',
            10 => 'Imunisasi',
            11 => 'Kematian',
            12 => 'Klaim Asuransi',
            13 => 'Kecelakaan Kerja',
            14 => 'Sakit Berkepanjangan',
            15 => 'Absensi Dokter Honorer',
            21 => 'Kategori Khusus',
        ];

        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun');
        $unitId = $request->input('unit_id');
        $search = $request->input('search');

        $units = Unit::all();

        $ringkasan = [];

        $authUser = Auth::guard('admin')->user() ?? Auth::guard('web')->user();
        $is_admin = Auth::guard('admin')->check();

        foreach ($kategoriList as $kategoriId => $kategoriNama) {
            $subkategoriData = \App\Models\SubKategori::where('kategori_id', $kategoriId)->get();

            $laporanQuery = \App\Models\LaporanBulanan::where('kategori_id', $kategoriId);

            if ($is_admin) {
                if ($unitId) {
                    $laporanQuery->where('unit_id', $unitId);
                }
            } else {
                $laporanQuery->where('unit_id', $authUser->unit_id);
            }

            if ($bulan) {
                $laporanQuery->where('bulan', $bulan);
            }
            if ($tahun) {
                $laporanQuery->where('tahun', $tahun);
            }

            $laporan = $laporanQuery->get();

            $subkategoriRingkasan = $subkategoriData->map(function ($sub) use ($laporan) {
                $jumlah = $laporan->where('subkategori_id', $sub->id)->sum('jumlah');
                return [
                    'id' => $sub->id,
                    'nama' => $sub->nama,
                    'total' => $jumlah,
                ];
            });

            $ringkasan[] = [
                'id' => $kategoriId,
                'nama' => $kategoriNama,
                'total' => $subkategoriRingkasan->sum('total'),
                'subkategori' => $subkategoriRingkasan,
            ];
        }

        // Tab obat
        $obats = collect();
        if ($tab === 'obat') {
            $obatQuery = Obat::query();

            if ($is_admin && $unitId) {
                $obatQuery->where('unit_id', $unitId);
            } elseif (!$is_admin) {
                $obatQuery->where('unit_id', $authUser->unit_id);
            }

            if ($search) {
                $obatQuery->where('nama_obat', 'like', '%' . $search . '%');
            }

            $obats = $obatQuery->get();
        }

        $viewData = compact('ringkasan', 'bulan', 'tahun', 'authUser', 'is_admin', 'units', 'unitId', 'tab', 'obats');

        if ($is_admin) {
            return view('admin-dashboard', $viewData);
        } else {
            return view('dashboard', $viewData);
        }
    }
}
