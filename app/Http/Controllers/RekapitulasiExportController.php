<?php

namespace App\Http\Controllers;

use App\Exports\ObatExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class RekapitulasiExportController extends Controller
{
    public function export(Request $request)
    {
        try {
            // Validasi input tanggal
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            // Ambil user login dan unit_id-nya
            $user = Auth::user();
            if (!$user) {
                return redirect()->route('login')->with('error', 'Sesi login sudah habis. Silakan login kembali.');
            }

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            // Batasi rentang tanggal maksimal 3 bulan
            if ($startDate->diffInMonths($endDate) > 3) {
                return back()->with('error', 'Range tanggal maksimal 3 bulan');
            }

            $filename = 'rekapitulasi-obat-' . $startDate->format('F-Y') . '.xlsx';

            // ✅ Kirim unit_id ke ObatExport agar data diambil dari tabel rekapitulasi_obat, bukan dari tabel obat
            return Excel::download(
                new ObatExport($startDate, $endDate, $user->unit_id),
                $filename
            );
        } catch (\Exception $e) {
            dd('Terjadi kesalahan yang tidak terduga: ' . $e->getMessage());
        }
    }
}
