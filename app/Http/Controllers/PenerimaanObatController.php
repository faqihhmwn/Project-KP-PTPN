<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PenerimaanObat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class PenerimaanObatController extends Controller
{

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => '❌ Tidak ada user yang login'
            ], 401);
        }

        $validated = Validator::make($request->all(), [
            'obat_id' => 'required|exists:obats,id',
            'jumlah_masuk' => 'required|integer|min:1',
            'tanggal_masuk' => 'required|date',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'success' => false,
                'message' => '❌ Validasi gagal',
                'errors' => $validated->errors()
            ], 422);
        }

        // ✅ Tambahan: Cek apakah bulan dikunci
        $tanggalMasuk = \Carbon\Carbon::parse($request->tanggal_masuk);
        $lockKey = 'obat_validasi_' . $tanggalMasuk->year . '_' . $tanggalMasuk->month;

        if (\Storage::exists('validasi/' . $lockKey . '.lock')) {
            return response()->json([
                'success' => false,
                'message' => '❌ Data bulan ini telah divalidasi dan dikunci. Tidak dapat menambahkan stok.'
            ], 403);
        }

        $penerimaan = new PenerimaanObat();
        $penerimaan->obat_id = $request->obat_id;
        $penerimaan->jumlah_masuk = $request->jumlah_masuk;
        $penerimaan->tanggal_masuk = $request->tanggal_masuk;
        $penerimaan->unit_id = Auth::user()->unit_id;
        $penerimaan->user_id = Auth::id();
        $penerimaan->save();

        return response()->json([
            'success' => true,
            'message' => '✅ Penerimaan obat berhasil disimpan.'
        ]);
    }
}
