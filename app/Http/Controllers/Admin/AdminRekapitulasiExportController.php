<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\ObatExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use App\Models\Unit;

class AdminRekapitulasiExportController extends Controller
{
    public function export(Request $request)
{
    try {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'unit_id' => 'required|exists:units,id', // ✅ validasi unit_id
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $unitId = $request->unit_id; // ✅ Ambil dari filter

        // Maksimal 3 bulan
        if ($startDate->diffInMonths($endDate) > 3) {
            return back()->with('error', 'Range tanggal maksimal 3 bulan');
        }

        $unitId = $request->get('unit_id');
        $unitName = Unit::find($unitId)?->nama ?? 'Unknown';

        $filename = 'rekapitulasi-obat-' . $startDate->format('F-Y') . '-unit-' . strtoupper(str_replace(' ', '-', $unitName)) . '.xlsx';

        return Excel::download(
            new ObatExport($startDate, $endDate, $unitId), // ✅ Gunakan unit_id dari request
            $filename
        );
    } catch (\Exception $e) {
        return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

}
