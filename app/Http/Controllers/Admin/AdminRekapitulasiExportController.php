<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ObatExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use App\Models\Obat;
use App\Models\RekapitulasiObat;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class AdminRekapitulasiExportController extends Controller
{
    public function export(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            // Ambil admin saat ini dan unit_id-nya
            $admin = Auth::guard('admin')->user();
            if (!$admin) {
                return redirect()->route('admin.login')->with('error', 'Sesi admin habis. Silakan login kembali.');
            }

            $unitId = $admin->unit_id;

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            if ($startDate->diffInMonths($endDate) > 3) {
                return back()->with('error', 'Range tanggal maksimal 3 bulan');
            }

            $obats = Obat::where('unit_id', $unitId)->get();

            $filename = 'rekapitulasi-obat-' . 
                        $startDate->format('F-Y') . 
                        '.xlsx';

            return Excel::download(
                new ObatExport($startDate, $endDate),
                $filename
            );
        } catch (\Exception $e) {
            dd('Terjadi kesalahan yang tidak terduga: ' . $e->getMessage());
        }
    }
}
