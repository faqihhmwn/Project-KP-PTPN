<?php

namespace App\Http\Controllers\Rekap;

use App\Exports\KapitasiExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Routing\Controller;

class KapitasiExportController extends Controller
{
    public function export(Request $request)
    {
        $tahun = $request->get('tahun');

        return Excel::download(
            new KapitasiExport($tahun),
            'biaya_pemakaian_kapitasi_' . $tahun . '.xlsx'
        );
    }
}
