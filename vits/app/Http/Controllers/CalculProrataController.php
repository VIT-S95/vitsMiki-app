<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CalculProrataController extends Controller
{
    public function index()
    {
        return view('calculs.prorata');
    }
}
