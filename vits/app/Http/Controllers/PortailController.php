<?php

namespace App\Http\Controllers;

class PortailController extends Controller
{
    public function index()
    {
        $client = auth()->user()->client;
        return view('portail.index', compact('client'));
    }
}
