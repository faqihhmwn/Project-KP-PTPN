<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LaporanBulanan;
use App\Models\InputManual; // Tambahkan ini
use App\Models\Unit;
use App\Models\Obat;
use App\Models\Kategori; // Tambahkan ini
use App\Exports\LaporanKesehatanRekapExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'laporan');
        $is_admin = Auth::guard('admin')->check();
        $authUser = Auth::guard('admin')->user() ?? Auth::guard('web')->user();

        // Mengambil semua kategori dari database
        $kategoriList = Kategori::with('subkategori')->get();
        $units = Unit::all();

        // Filter
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

        $ringkasan = [];

        if ($tab === 'laporan') {
            foreach ($kategoriList as $kategori) {
                $subkategoriData = $kategori->subkategori;
                $laporan = collect();

                // --- PERUBAHAN UTAMA DI SINI ---
                if ($kategori->id == 21) { // Jika KATEGORI KHUSUS
                    $query = InputManual::query(); // Ambil dari tabel input_manual

                    // Terapkan filter yang sama
                    if ($is_admin && $unitId) $query->where('unit_id', $unitId);
                    if (!$is_admin) $query->where('unit_id', $authUser->unit_id);
                    if ($bulan) $query->where('bulan', $bulan);
                    if ($tahun) $query->where('tahun', $tahun);

                    $laporan = $query->get();

                    // Hitung berdasarkan jumlah baris (count), bukan menjumlahkan kolom 'jumlah'
                    $subkategoriRingkasan = $subkategoriData->map(function ($sub) use ($laporan) {
                        $jumlah = $laporan->where('subkategori_id', $sub->id)->count();
                        return ['nama' => $sub->nama, 'total' => $jumlah];
                    });
                } else { // Untuk semua kategori lainnya
                    $query = LaporanBulanan::where('kategori_id', $kategori->id); // Ambil dari laporan_bulanan

                    // Terapkan filter
                    if ($is_admin && $unitId) $query->where('unit_id', $unitId);
                    if (!$is_admin) $query->where('unit_id', $authUser->unit_id);
                    if ($bulan) $query->where('bulan', $bulan);
                    if ($tahun) $query->where('tahun', $tahun);

                    $laporan = $query->get();

                    // Hitung dengan menjumlahkan kolom 'jumlah'
                    $subkategoriRingkasan = $subkategoriData->map(function ($sub) use ($laporan) {
                        $jumlah = $laporan->where('subkategori_id', $sub->id)->sum('jumlah');
                        return ['nama' => $sub->nama, 'total' => $jumlah];
                    });
                }
                // --- AKHIR PERUBAHAN ---

                $ringkasan[] = [
                    'nama' => $kategori->nama,
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

            $obats = $obatQuery->get();
        }

        $viewData = compact(
            'ringkasan',
            'bulan',
            'tahun',
            'authUser',
            'is_admin',
            'units',
            'unitId',
            'searchSubkategori',
            'tab',
            'obats',
            'unitIdObat',
            'bulanObat',
            'tahunObat',
            'searchNamaObat'
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
        $isAdmin = Auth::guard('admin')->check();
        $unitId = $isAdmin ? null : Auth::guard('web')->user()->unit_id;

        $namaBulan = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->format('F');
        $fileName = 'REKAP_LAPORAN_KESEHATAN_' . strtoupper($namaBulan) . '_' . $tahun . '.xlsx';

        return Excel::download(new \App\Exports\LaporanKesehatanRekapExport($bulan, $tahun, $unitId), $fileName);
    }
}
