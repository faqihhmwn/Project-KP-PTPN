<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\PenerimaanObat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class AdminPenerimaanObatController extends Controller
{
    public function store(Request $request)
    {
        \Log::info('🔍 MASUK KE store() [ADMIN]', $request->all());

        if (!Auth::guard('admin')->check()) {
            \Log::warning('❌ Admin tidak login');
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
            \Log::error('❌ Validasi gagal [ADMIN]:', $validated->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => '❌ Validasi gagal',
                'errors' => $validated->errors()
            ], 422);
        }

        $tanggalMasuk = Carbon::parse($request->tanggal_masuk);
        $lockKey = 'obat_validasi_' . $tanggalMasuk->year . '_' . $tanggalMasuk->month;

        if (Storage::exists('validasi/' . $lockKey . '.lock')) {
            \Log::info('🔒 Data sudah divalidasi, tidak bisa tambah [ADMIN]');
            return response()->json([
                'success' => false,
                'message' => '❌ Data bulan ini telah divalidasi dan dikunci.'
            ], 403);
        }

        try {
            $admin = Auth::guard('admin')->user();

            $penerimaan = PenerimaanObat::updateOrCreate(
                [
                    'obat_id' => $request->obat_id,
                    'tanggal_masuk' => $request->tanggal_masuk,
                    'unit_id' => $admin->unit_id,
                ],
                [
                    'jumlah_masuk' => $request->jumlah_masuk,
                    'user_id' => $admin->id,
                ]
            );

            \Log::info('✅ Penerimaan berhasil disimpan [ADMIN]');
            return response()->json([
                'success' => true,
                'message' => '✅ Penerimaan obat berhasil disimpan.'
            ]);
        } catch (\Exception $e) {
            \Log::error('❌ ERROR DB [ADMIN]: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '❌ Gagal menyimpan ke database.',
            ], 500);
        }
    }
}
