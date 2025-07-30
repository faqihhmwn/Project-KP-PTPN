<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\PenerimaanObat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminPenerimaanObatController extends Controller
{
    public function store(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return response()->json([
                'success' => false,
                'message' => '❌ Tidak ada admin yang login'
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

        $penerimaan = new PenerimaanObat();
        $penerimaan->obat_id = $request->obat_id;
        $penerimaan->jumlah_masuk = $request->jumlah_masuk;
        $penerimaan->tanggal_masuk = $request->tanggal_masuk;
        $penerimaan->unit_id = Auth::guard('admin')->user()->unit_id;
        $penerimaan->user_id = Auth::guard('admin')->id();
        $penerimaan->save();

        return response()->json([
            'success' => true,
            'message' => '✅ Penerimaan obat berhasil disimpan.'
        ]);
    }
}
