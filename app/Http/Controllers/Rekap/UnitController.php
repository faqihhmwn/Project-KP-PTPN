<?php

namespace App\Http\Controllers\Rekap;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function biayaKesehatan()
    {
        return view('rekap.biaya-kesehatan');
    }

    public function bpjs()
    {
        return view('rekap.iuran-bpjs');
    }
}
