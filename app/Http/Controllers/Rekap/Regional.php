<?php

namespace App\Http\Controllers\Rekap;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Regional extends Controller
{
    public function index()
    {
        return view('rekap.regional');
    }
}
