<?php

namespace App\Http\Controllers;

use App\Exports\ObatExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log; // Pastikan ini di-import

class RekapitulasiExportController extends Controller
{
    public function export(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'include_daily' => 'nullable|boolean' // Tambahkan validasi ini jika export Anda mendukungnya
            ]);

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            // Validasi maksimal 3 bulan, seperti yang sudah ada di ObatController
            if ($startDate->diffInMonths($endDate) > 3) {
                return redirect()->back()->with('error', 'Range tanggal maksimal 3 bulan.');
            }

            $filename = "laporan-obat-{$startDate->format('Y-m-d')}-to-{$endDate->format('Y-m-d')}.xlsx";

            // Pastikan ObatExport juga menerima parameter $includeDailyData jika ada
            $includeDailyData = $request->boolean('include_daily', false);
            return Excel::download(new ObatExport($startDate, $endDate, $includeDailyData), $filename);
        } catch (\Exception $e) {
            // Log error untuk debugging lebih lanjut
            Log::error('Export rekapitulasi error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception_trace' => $e->getTraceAsString()
            ]);
            // Menggunakan redirect()->back() agar konsisten
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengekspor data: ' . $e->getMessage());
        }
    }
}
