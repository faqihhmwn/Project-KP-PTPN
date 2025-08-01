<?php

namespace App\Http\Controllers;

use App\Exports\ObatExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RekapitulasiExportController extends Controller
{
    public function export(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);
            
            // Ambil user saat ini dan unit_id nya
            $user = Auth::user();
            if (!$user) {
                return redirect()->route('login')->with('error', 'Sesi login sudah habis. Silakan login kembali.');
            }
            $userUnitId = $user->unit_id;

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            // Validasi range tanggal maksimal 3 bulan
            if ($startDate->diffInMonths($endDate) > 3) {
                return back()->with('error', 'Range tanggal maksimal 3 bulan');
            }

            // Ambil data obat untuk unit tersebut
            $obats = Obat::where('unit_id', $userUnitId)->get();
            
            // Pastikan setiap obat memiliki rekapitulasi dengan harga yang benar
            foreach ($obats as $obat) {
                $rekap = RekapitulasiObat::firstOrCreate(
                    [
                        'obat_id' => $obat->id,
                        'tanggal' => now()->format('Y-m-d'),
                        'unit_id' => $userUnitId
                    ],
                    [
                        'harga_satuan' => $obat->harga_satuan,
                        'user_id' => $user->id
                    ]
                );
            }

            $filename = 'rekapitulasi-obat-' . 
                        $startDate->format('F-Y') . 
                        '.xlsx';

            return Excel::download(
                new ObatExport($startDate, $endDate),
                $filename
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
