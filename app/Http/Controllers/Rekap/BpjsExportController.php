<?php

namespace App\Http\Controllers\Rekap;

use App\Exports\BpjsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Routing\Controller;

class BpjsExportController extends Controller
{
    public function export(Request $request)
    {
        $tahun = $request->get('tahun');

        return Excel::download(
            new BpjsExport($tahun),
            'rekap_iuran_bpjs_' . $tahun . '.xlsx'
        );
    }
}
