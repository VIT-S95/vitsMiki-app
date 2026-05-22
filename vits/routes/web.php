<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', function (Request $request) {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});

require __DIR__.'/auth.php';
use App\Http\Controllers\ClientController;
Route::middleware(['auth'])->group(function () {
    Route::resource('clients', ClientController::class);
});

use App\Http\Controllers\ContratController;
Route::resource('contrats', ContratController::class)->middleware('auth');
