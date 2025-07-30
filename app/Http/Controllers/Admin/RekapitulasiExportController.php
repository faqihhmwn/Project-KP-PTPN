<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ObatExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AdminRekapitulasiExportController extends \App\Http\Controllers\Controller
{
    public function export(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            // Validasi range tanggal maksimal 3 bulan
            if ($startDate->diffInMonths($endDate) > 3) {
                return back()->with('error', 'Range tanggal maksimal 3 bulan');
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
