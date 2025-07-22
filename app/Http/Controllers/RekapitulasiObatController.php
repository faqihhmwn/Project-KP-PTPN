<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RekapitulasiObat;
use Illuminate\Support\Facades\Validator; // Pastikan ini di-import
use Illuminate\Support\Facades\Log; // Pastikan ini di-import
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Unit;

class RekapitulasiObatController extends Controller
{
    public function storeOrUpdate(Request $request)
    {
        // --- 1. Logging Data yang Diterima (Untuk Debugging) ---
        // Ini akan membantu Anda melihat apakah data 'tanggal' benar-benar dikirim dari frontend.
        Log::info('Data diterima untuk storeOrUpdate:', $request->all());

        // Jika bulk, simpan banyak data sekaligus
        if ($request->has('bulk')) {
            $bulk = $request->input('bulk');
            $saved = 0;
            $errors = [];

            foreach ($bulk as $item) {
                // Validasi manual tiap item
                $validator = Validator::make($item, [
                    'obat_id' => 'required|integer',
                    'tanggal' => 'required|date_format:Y-m-d', // <-- PENTING: Pastikan ini
                    'jumlah_keluar' => 'required|integer|min:0',
                    'stok_awal' => 'required|integer|min:0',
                    'sisa_stok' => 'required|integer|min:0',
                    'total_biaya' => 'required|integer|min:0',
                    'bulan' => 'required|integer|min:1|max:12',
                    'tahun' => 'required|integer|min:2000|max:' . (date('Y') + 1),
                ]);

                if ($validator->fails()) {
                    $errors[] = ['item' => $item, 'errors' => $validator->errors()->toArray()];
                    Log::warning('Validasi gagal untuk item bulk:', $item);
                    continue;
                }

                $validated = $validator->validated();

                try {
                    RekapitulasiObat::updateOrCreate(
                        [
                            'obat_id' => $validated['obat_id'],
                            'tanggal' => $validated['tanggal'],
                            'bulan' => $validated['bulan'],
                            'tahun' => $validated['tahun'],
                            'unit_id' => Auth::user()->unit_id,
                        ],
                        [
                            'user_id' => Auth::id(),
                            'stok_awal' => $validated['stok_awal'],
                            'jumlah_keluar' => $validated['jumlah_keluar'],
                            'sisa_stok' => $validated['sisa_stok'],
                            'total_biaya' => $validated['total_biaya'],
                        ]
                    );
                    $saved++;
                } catch (\Exception $e) {
                    $errors[] = ['item' => $item, 'error_db' => $e->getMessage()];
                    Log::error('Gagal menyimpan item bulk ke DB: ' . $e->getMessage(), ['item' => $item]);
                }
            }

            if (!empty($errors)) {
                return response()->json(['success' => false, 'message' => 'Beberapa item gagal disimpan.', 'saved_count' => $saved, 'errors' => $errors], 400);
            }
            return response()->json(['success' => true, 'count' => $saved, 'message' => 'Data bulk berhasil disimpan/diperbarui.']);
        }

        // Single data (auto-save) - ini yang dipanggil dari JavaScript di View Anda
        try {
            $validated = $request->validate([
                'obat_id' => 'required|integer',
                'tanggal' => 'required|date_format:Y-m-d', // <-- PENTING: Pastikan ini
                'jumlah_keluar' => 'required|integer|min:0',
                'stok_awal' => 'required|integer|min:0',
                'sisa_stok' => 'required|integer|min:0',
                'total_biaya' => 'required|integer|min:0',
                'bulan' => 'required|integer|min:1|max:12',
                'tahun' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            ]);

            $rekap = RekapitulasiObat::updateOrCreate(
                [
                    'obat_id' => $validated['obat_id'],
                    'tanggal' => $validated['tanggal'],
                    'bulan' => $validated['bulan'], // <-- PENTING: Pastikan ini di kunci
                    'tahun' => $validated['tahun'], // <-- PENTING: Pastikan ini di kunci
                ],
                [
                    'stok_awal' => $validated['stok_awal'],
                    'jumlah_keluar' => $validated['jumlah_keluar'],
                    'sisa_stok' => $validated['sisa_stok'],
                    'total_biaya' => $validated['total_biaya'],
                ]
            );
            return response()->json(['success' => true, 'rekap' => $rekap, 'message' => 'Data rekapitulasi harian berhasil disimpan/diperbarui.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validasi gagal untuk single data:', $e->errors());
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan single data rekapitulasi obat: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'error_trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan server: ' . $e->getMessage()], 500);
        }
    }
}
