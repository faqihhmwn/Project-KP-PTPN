<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RekapitulasiObat;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Unit;
use App\Models\Obat;

class AdminRekapitulasiObatController extends Controller
{
    public function index(Request $request)
    {
        $unitId = $request->input('unit_id');
        $bulan = $request->input('bulan', date('n'));
        $tahun = $request->input('tahun', date('Y'));
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
        $units = Unit::all(); 

        // Jika belum pilih unit, tampilkan halaman tanpa data
        if (!$unitId) {
            return view('admin.obat.rekapitulasi', compact('bulan', 'tahun', 'daysInMonth', 'units'));
        }

        // Jika sudah pilih unit, ambil data sesuai unit
        $obats = Obat::where('unit_id', $unitId)
            ->with(['rekapitulasiObatByUnit' => function ($query) use ($bulan, $tahun) {
                $query->where('bulan', $bulan)->where('tahun', $tahun);
            }])
            ->get();

        $rekapitulasi = RekapitulasiObat::with(['obat', 'user'])
            ->where('unit_id', $unitId)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get();

        $isLocked = Storage::exists('validasi/obat_validasi_' . $tahun . '_' . $bulan . '.lock');

        return view('admin.obat.rekapitulasi', compact(
            'obats',
            'rekapitulasi',
            'bulan',
            'tahun',
            'daysInMonth',
            'isLocked',
            'units',      
            'unitId'      
        ));
    }

    public function storeOrUpdate(Request $request)
    {
        Log::info('Data diterima untuk storeOrUpdate:', $request->all());

        $unitId = Auth::guard('admin')->user()->unit_id;
        $userId = Auth::guard('admin')->id();

        if ($request->has('bulk')) {
            $bulk = $request->input('bulk');
            $saved = 0;
            $errors = [];

            foreach ($bulk as $item) {
                $validator = Validator::make($item, [
                    'obat_id' => 'required|integer',
                    'tanggal' => 'required|date_format:Y-m-d',
                    'jumlah_keluar' => 'required|integer|min:0',
                    'stok_awal' => 'required|integer|min:0',
                    'bulan' => 'required|integer|min:1|max:12',
                    'tahun' => 'required|integer|min:2000|max:' . (date('Y') + 1),
                ]);

                if ($validator->fails()) {
                    $errors[] = ['item' => $item, 'errors' => $validator->errors()->toArray()];
                    continue;
                }

                $validated = $validator->validated();

                try {
                    $penerimaan = \App\Models\PenerimaanObat::where('obat_id', $validated['obat_id'])
                        ->where('unit_id', $unitId)
                        ->whereDate('tanggal_masuk', $validated['tanggal'])
                        ->sum('jumlah_masuk');

                    $obat = Obat::find($validated['obat_id']);
                    $hargaSatuan = $obat->harga_satuan ?? 0;

                    $sisaStok = max(0, $validated['stok_awal'] + $penerimaan - $validated['jumlah_keluar']);
                    $totalBiaya = $validated['jumlah_keluar'] * $hargaSatuan;

                    RekapitulasiObat::updateOrCreate(
                        [
                            'obat_id' => $validated['obat_id'],
                            'tanggal' => $validated['tanggal'],
                            'bulan' => $validated['bulan'],
                            'tahun' => $validated['tahun'],
                            'unit_id' => $unitId,
                        ],
                        [
                            'user_id' => $userId,
                            'stok_awal' => $validated['stok_awal'],
                            'jumlah_keluar' => $validated['jumlah_keluar'],
                            'sisa_stok' => $sisaStok,
                            'total_biaya' => $totalBiaya,
                            'harga_satuan' => $hargaSatuan,
                        ]
                    );
                    $saved++;
                } catch (\Exception $e) {
                    $errors[] = ['item' => $item, 'error_db' => $e->getMessage()];
                }
            }

            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Beberapa item gagal disimpan.',
                    'saved_count' => $saved,
                    'errors' => $errors
                ], 400);
            }

            return response()->json([
                'success' => true,
                'count' => $saved,
                'message' => 'Data bulk berhasil disimpan/diperbarui.'
            ]);
        }

        try {
            $validated = $request->validate([
                'obat_id' => 'required|integer',
                'tanggal' => 'required|date_format:Y-m-d',
                'jumlah_keluar' => 'required|integer|min:0',
                'stok_awal' => 'required|integer|min:0',
                'bulan' => 'required|integer|min:1|max:12',
                'tahun' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            ]);

            $penerimaan = \App\Models\PenerimaanObat::where('obat_id', $validated['obat_id'])
                ->where('unit_id', $unitId)
                ->whereDate('tanggal_masuk', $validated['tanggal'])
                ->sum('jumlah_masuk');

            $obat = Obat::find($validated['obat_id']);
            $hargaSatuan = $obat->harga_satuan ?? 0;

            $sisaStok = max(0, $validated['stok_awal'] + $penerimaan - $validated['jumlah_keluar']);
            $totalBiaya = $validated['jumlah_keluar'] * $hargaSatuan;

            $rekap = RekapitulasiObat::updateOrCreate(
                [
                    'obat_id' => $validated['obat_id'],
                    'tanggal' => $validated['tanggal'],
                    'bulan' => $validated['bulan'],
                    'tahun' => $validated['tahun'],
                    'unit_id' => $unitId,
                ],
                [
                    'user_id' => $userId,
                    'stok_awal' => $validated['stok_awal'],
                    'jumlah_keluar' => $validated['jumlah_keluar'],
                    'sisa_stok' => $sisaStok,
                    'total_biaya' => $totalBiaya,
                    'harga_satuan' => $hargaSatuan,
                ]
            );

            Log::info('Menyimpan rekapitulasi dengan data:', [
                'obat_id' => $validated['obat_id'],
                'tanggal' => $validated['tanggal'],
                'harga_satuan' => $hargaSatuan,
                'rekap_id' => $rekap->id
            ]);

            return response()->json(['success' => true, 'rekap' => $rekap, 'message' => 'Data rekapitulasi harian berhasil disimpan/diperbarui.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan server: ' . $e->getMessage()], 500);
        }
    }
}
