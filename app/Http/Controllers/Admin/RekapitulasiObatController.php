<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RekapitulasiObat;
use Illuminate\Support\Facades\Validator; // Pastikan ini di-import
use Illuminate\Support\Facades\Log; // Pastikan ini di-import
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Unit;
use App\Models\Obat;
use Illuminate\Support\Facades\DB;

class RekapitulasiObatController extends Controller
{

    public function index(Request $request)
    {
        $unitId = Auth::user()->unit_id;

        $bulan = $request->input('bulan', date('n'));
        $tahun = $request->input('tahun', date('Y'));
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

        $obats = Obat::where('unit_id', Auth::user()->unit_id)
            ->with(['rekapitulasiObatByUnit' => function ($query) use ($bulan, $tahun) {
                $query->where('bulan', $bulan)
                    ->where('tahun', $tahun);
            }])
            ->get();


        $rekapitulasi = RekapitulasiObat::with(['obat', 'user'])
            ->where('unit_id', $unitId)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get();

        return view('rekapitulasi-obat', compact('obats', 'rekapitulasi', 'bulan', 'tahun', 'daysInMonth'));
    }

    public function storeOrUpdate(Request $request)
    {
        // 1. Validasi data yang masuk
        $validated = $request->validate([
            'bulk' => 'required|array',
            'bulk.*.obat_id' => 'required|integer|exists:obats,id',
            'bulk.*.tanggal' => 'required|date',
            'bulk.*.jumlah_keluar' => 'required|integer|min:0',
            'unit_id' => 'required|integer|exists:units,id',
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer',
        ]);

        $unitId = $validated['unit_id'];
        $bulan = $validated['bulan'];
        $tahun = $validated['tahun'];
        
        // Kumpulkan ID obat yang terpengaruh untuk pembaruan
        $affectedObatIds = collect($validated['bulk'])->pluck('obat_id')->unique();

        DB::beginTransaction();
        try {
            // Proses setiap baris data yang dikirim dari frontend
            foreach ($validated['bulk'] as $data) {
                RekapitulasiObat::updateOrCreate(
                    [
                        'obat_id' => $data['obat_id'],
                        'tanggal' => $data['tanggal'],
                        'unit_id' => $unitId,
                    ],
                    [
                        'bulan' => $bulan,
                        'tahun' => $tahun,
                        'jumlah_keluar' => $data['jumlah_keluar'],
                    ]
                );
            }

            // --- PERBAIKAN UTAMA: Perbarui Stok Sisa di Tabel Obat ---
            foreach ($affectedObatIds as $obatId) {
                $obat = Obat::find($obatId);
                if ($obat) {
                    // Hitung total pemakaian dari semua rekapitulasi untuk obat ini
                    $totalKeluar = RekapitulasiObat::where('obat_id', $obatId)
                                                    ->where('unit_id', $unitId)
                                                    ->sum('jumlah_keluar');
                    
                    // Hitung sisa stok yang baru
                    $stokSisaBaru = $obat->stok_awal - $totalKeluar;

                    // Perbarui kolom 'stok_sisa' di tabel 'obats'
                    $obat->stok_sisa = $stokSisaBaru;
                    $obat->save();
                }
            }
            // --- AKHIR PERBAIKAN ---

            DB::commit();

            return response()->json(['message' => 'Data rekapitulasi berhasil disimpan dan stok telah diperbarui!'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan rekapitulasi: ' . $e->getMessage());

            return response()->json(['message' => 'Terjadi kesalahan di server saat menyimpan data.'], 500);
        }
    }
}