<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\LaporanBulanan;
use App\Models\Subkategori;


class DashboardController extends Controller
{

    public function index(Request $request)
    {
        $userUnitId = Auth::user()->unit_id;

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

        $ringkasan = [];

        foreach ($kategoriList as $kategoriId => $kategoriNama) {
            $subkategoriData = \App\Models\SubKategori::where('kategori_id', $kategoriId)->get();

            $laporan = \App\Models\LaporanBulanan::where('kategori_id', $kategoriId)
                ->where('unit_id', $userUnitId);

            if ($bulan) $laporan->where('bulan', $bulan);
            if ($tahun) $laporan->where('tahun', $tahun);

            $laporan = $laporan->get();

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

        return view('dashboard', compact('ringkasan', 'bulan', 'tahun'));
    }
}
