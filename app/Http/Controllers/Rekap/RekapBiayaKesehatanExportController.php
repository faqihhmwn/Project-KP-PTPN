<?php 

namespace App\Http\Controllers\Rekap;

use App\Exports\RekapBiayaKesehatanExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Routing\Controller;

class RekapBiayaKesehatanExportController extends Controller
{
    public function export(Request $request)
    {
        $tahun = $request->get('tahun');

        return Excel::download(
            new RekapBiayaKesehatanExport($tahun),
            'rekap_biaya_kesehatan_' . $tahun . '.xlsx'
        );
    }
}
