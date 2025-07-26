<?php

namespace App\Http\Controllers;

use App\Exports\ObatExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Excel; // Pastikan menggunakan Facades\Excel
use Illuminate\Support\Facades\Log;   // Import Log Facade

class RekapitulasiExportController extends Controller
{
    public function export(Request $request)
    {
        try {
            // Validasi input, termasuk 'include_daily' yang baru
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'include_daily' => 'nullable|boolean' // <-- Tambahan ini
            ]);

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            // Ambil nilai boolean untuk include_daily, defaultnya false
            $includeDailyData = $request->boolean('include_daily', false); // <-- Tambahan ini

            // Validasi range tanggal maksimal 3 bulan
            if ($startDate->diffInMonths($endDate) > 3) {
                return back()->with('error', 'Range tanggal maksimal 3 bulan.');
            }

            // Ubah format nama file agar lebih deskriptif untuk rentang tanggal
            $filename = "laporan-rekapitulasi-obat-{$startDate->format('Y-m-d')}-to-{$endDate->format('Y-m-d')}.xlsx"; // <-- Perbaikan ini

            // Panggil ObatExport dengan semua parameter yang diperlukan
            return Excel::download(
                new ObatExport($startDate, $endDate, $includeDailyData), // <-- Tambahan argumen ketiga
                $filename
            );
        } catch (\Exception $e) {
            // Log error untuk debugging lebih lanjut
            Log::error('Export rekapitulasi error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception_trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Terjadi kesalahan saat mengekspor data: ' . $e->getMessage());
        }
    }
}