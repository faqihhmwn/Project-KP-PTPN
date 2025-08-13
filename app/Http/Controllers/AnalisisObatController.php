<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RekapitulasiObat;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AnalisisObatExport;

class AnalisisObatController extends Controller
{
        public function index(Request $request)
        {
            $query = RekapitulasiObat::with(['obat', 'unit'])
                ->where('unit_id', auth()->user()->unit_id); // Filter sesuai unit user login

            // ✅ Filter: Nama obat
            if ($request->filled('obat')) {
                $query->whereHas('obat', function ($q) use ($request) {
                    $q->where('nama_obat', 'like', '%' . $request->obat . '%');
                });
            }

            // ✅ Filter: Jenis obat
            if ($request->filled('jenis')) {
                $query->whereHas('obat', function ($q) use ($request) {
                    $q->where('jenis_obat', 'like', '%' . $request->jenis . '%');
                });
            }

            // ✅ Filter: Periode
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $start = Carbon::parse($request->start_date)->startOfDay();
                $end = Carbon::parse($request->end_date)->endOfDay();
                $query->whereBetween('tanggal', [$start, $end]);
            }

            $data = $query->orderBy('tanggal', 'asc')->get();

            return view('obat.analisis-obat', [
                'data' => $data,
                'request' => $request
            ]);
        }



    public function export(Request $request)
    {
        return Excel::download(new AnalisisObatExport($request), 'analisis-obat.xlsx');
    }
}
