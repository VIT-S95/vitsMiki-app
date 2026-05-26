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

use App\Http\Controllers\InterventionController;
Route::middleware(['auth'])->group(function () {
    Route::post('/periodes/traiter', [PeriodeAjustementController::class, 'traiter'])->name('periodes.traiter');
    Route::get('/interventions', [InterventionController::class, 'index'])->name('interventions.index');
    Route::get('/interventions/create', [InterventionController::class, 'create'])->name('interventions.create');
    Route::post('/interventions', [InterventionController::class, 'store'])->name('interventions.store');
    Route::get('/interventions/{intervention}/edit', [InterventionController::class, 'edit'])->name('interventions.edit');
    Route::put('/interventions/{intervention}', [InterventionController::class, 'update'])->name('interventions.update');
    Route::delete('/interventions/{intervention}', [InterventionController::class, 'destroy'])->name('interventions.destroy');
});

use App\Http\Controllers\KizeoController;
Route::middleware(['auth'])->group(function () {
    Route::post('/kizeo/forcer', [KizeoController::class, 'forcer'])->name('kizeo.forcer');
});

use App\Http\Controllers\PdfController;
use App\Http\Controllers\PeriodeAjustementController;
Route::middleware(['auth'])->group(function () {
    Route::get('/contrats/{contrat}/pdf', [PdfController::class, 'rapport'])->name('contrats.pdf');
});

use App\Http\Controllers\ParametreController;
Route::middleware(['auth'])->group(function () {
    Route::get('/parametres', [ParametreController::class, 'index'])->name('parametres.index');
    Route::post('/parametres', [ParametreController::class, 'update'])->name('parametres.update');
    Route::delete('/parametres/logo', [ParametreController::class, 'deleteLogo'])->name('parametres.logo.delete');
    Route::post('/parametres/motifs', [ParametreController::class, 'updateMotifs'])->name('parametres.motifs');
});
