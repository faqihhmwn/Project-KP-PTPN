<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LaporanBulanan;
use App\Models\Unit;
use App\Models\Obat;
use Illuminate\Support\Str;
use App\Exports\LaporanKesehatanRekapExport;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'laporan');

        $kategoriList = [
            1 => 'Kependudukan', 2 => 'Penyakit', 3 => 'Opname', 4 => 'Penyakit Kronis',
            5 => 'Konsultasi Klinik', 6 => 'Cuti Sakit', 7 => 'Peserta KB', 8 => 'Metode KB',
            9 => 'Kehamilan', 10 => 'Imunisasi', 11 => 'Kematian', 12 => 'Klaim Asuransi',
            13 => 'Kecelakaan Kerja', 14 => 'Sakit Berkepanjangan', 15 => 'Absensi Dokter Honorer',
            21 => 'Kategori Khusus',
        ];

        // Filter untuk Laporan
        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun');
        $unitId = $request->input('unit_id');
        $searchSubkategori = $request->input('search');

        // Filter terpisah untuk Obat
        $unitIdObat = $request->input('unit_id_obat');
        $bulanObat = $request->input('bulan_obat');
        $tahunObat = $request->input('tahun_obat');
        $searchNamaObat = $request->input('search_nama');
        $searchJenisObat = $request->input('search_jenis');
        
        $units = Unit::all();
        $authUser = Auth::guard('admin')->user() ?? Auth::guard('web')->user();
        $is_admin = Auth::guard('admin')->check();

        // Logika untuk Tab Laporan
        $ringkasan = [];
        if ($tab === 'laporan') {
            foreach ($kategoriList as $kategoriId => $kategoriNama) {
                $subkategoriData = \App\Models\SubKategori::where('kategori_id', $kategoriId)->get();
                $laporanQuery = \App\Models\LaporanBulanan::where('kategori_id', $kategoriId);

                if ($is_admin && $unitId) $laporanQuery->where('unit_id', $unitId);
                if (!$is_admin) $laporanQuery->where('unit_id', $authUser->unit_id);
                if ($bulan) $laporanQuery->where('bulan', $bulan);
                if ($tahun) $laporanQuery->where('tahun', $tahun);

                $laporan = $laporanQuery->get();
                $subkategoriRingkasan = $subkategoriData->map(function ($sub) use ($laporan) {
                    return ['nama' => $sub->nama, 'total' => $laporan->where('subkategori_id', $sub->id)->sum('jumlah')];
                });

                $ringkasan[] = [
                    'nama' => $kategoriNama,
                    'total' => $subkategoriRingkasan->sum('total'),
                    'subkategori' => $subkategoriRingkasan,
                ];
            }
        }

        // Logika untuk Tab Obat
        $obats = collect();
        if ($tab === 'obat') {
            $obatQuery = Obat::query();

            if ($is_admin && $unitIdObat) $obatQuery->where('unit_id', $unitIdObat);
            if (!$is_admin) $obatQuery->where('unit_id', $authUser->unit_id);
            if ($searchNamaObat) $obatQuery->where('nama_obat', 'like', '%' . $searchNamaObat . '%');
            if ($searchJenisObat) $obatQuery->where('jenis_obat', 'like', '%' . $searchJenisObat . '%');

            // Filter bulan dan tahun pada rekapitulasi obat (jika diperlukan)
            // Untuk saat ini, kita hanya filter berdasarkan unit dan nama
            $obats = $obatQuery->get();
        }

        $viewData = compact(
            'ringkasan', 'bulan', 'tahun', 'authUser', 'is_admin', 'units', 'unitId', 'searchSubkategori',
            'tab', 'obats', 'unitIdObat', 'bulanObat', 'tahunObat', 'searchNamaObat'
        );

        return $is_admin ? view('admin-dashboard', $viewData) : view('dashboard', $viewData);
    }

    public function exportRekap(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2000',
        ]);

        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun');
        
        $namaBulan = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->format('F');
        $fileName = 'REKAP_LAPORAN_KESEHATAN_' . strtoupper($namaBulan) . '_' . $tahun . '.xlsx';

        return Excel::download(new LaporanKesehatanRekapExport($bulan, $tahun), $fileName);
    }
}