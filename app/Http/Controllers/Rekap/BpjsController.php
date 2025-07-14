<?php

namespace App\Http\Controllers\Rekap;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BpjsController extends Controller
{
    public function index()
    {
        return view('rekap.iuran-bpjs');
    }
}
