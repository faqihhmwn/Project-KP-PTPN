<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RekapitulasiObat;

class RekapitulasiObatController extends Controller
{
    public function storeOrUpdate(Request $request)
    {
        // Jika bulk, simpan banyak data sekaligus
        if ($request->has('bulk')) {
            $bulk = $request->input('bulk');
            $saved = 0;
            foreach ($bulk as $item) {
                // Validasi manual tiap item
                $validated = validator($item, [
                    'obat_id' => 'required|integer',
                    'tanggal' => 'required|date',
                    'jumlah_keluar' => 'required|integer|min:0',
                    'stok_awal' => 'required|integer|min:0',
                    'sisa_stok' => 'required|integer|min:0',
                    'total_biaya' => 'required|integer|min:0',
                    'bulan' => 'required|integer',
                    'tahun' => 'required|integer',
                ])->validate();
                RekapitulasiObat::updateOrCreate(
                    [
                        'obat_id' => $validated['obat_id'],
                        'tanggal' => $validated['tanggal'],
                    ],
                    [
                        'stok_awal' => $validated['stok_awal'],
                        'jumlah_keluar' => $validated['jumlah_keluar'],
                        'sisa_stok' => $validated['sisa_stok'],
                        'total_biaya' => $validated['total_biaya'],
                        'bulan' => $validated['bulan'],
                        'tahun' => $validated['tahun'],
                    ]
                );
                $saved++;
            }
            return response()->json(['success' => true, 'count' => $saved]);
        }
        // Single data (auto-save)
        $validated = $request->validate([
            'obat_id' => 'required|integer',
            'tanggal' => 'required|date',
            'jumlah_keluar' => 'required|integer|min:0',
            'stok_awal' => 'required|integer|min:0',
            'sisa_stok' => 'required|integer|min:0',
            'total_biaya' => 'required|integer|min:0',
            'bulan' => 'required|integer',
            'tahun' => 'required|integer',
        ]);
        $rekap = RekapitulasiObat::updateOrCreate(
            [
                'obat_id' => $validated['obat_id'],
                'tanggal' => $validated['tanggal'],
            ],
            [
                'stok_awal' => $validated['stok_awal'],
                'jumlah_keluar' => $validated['jumlah_keluar'],
                'sisa_stok' => $validated['sisa_stok'],
                'total_biaya' => $validated['total_biaya'],
                'bulan' => $validated['bulan'],
                'tahun' => $validated['tahun'],
            ]
        );
        return response()->json(['success' => true, 'rekap' => $rekap]);
    }
}
