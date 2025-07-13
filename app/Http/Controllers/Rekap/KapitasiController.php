<?php

namespace App\Http\Controllers\Rekap;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KapitasiController extends Controller
{
    public function index()
    {
        return view('rekap.kapitasi');
    }
}
