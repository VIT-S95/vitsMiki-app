<?php
namespace App\Http\Controllers;
use App\Services\KizeoService;
use Illuminate\Http\Request;

class KizeoController extends Controller
{
    public function forcer(KizeoService $kizeo)
    {
        $result = $kizeo->importUnread();
        return redirect()->route('dashboard')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
