<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PeriodeAjustementController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect()->route('login');
});

use App\Http\Controllers\Auth\AuthenticatedSessionController;
Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.post');

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
use App\Http\Controllers\FusionController;
Route::middleware(['auth', 'admin.only'])->group(function () {
    Route::get('/clients/fusion', [FusionController::class, 'index'])->name('clients.fusion');
    Route::post('/clients/fusion', [FusionController::class, 'fusionner'])->name('clients.fusion.store');
    Route::post('/clients/fusion/ignorer', [FusionController::class, 'ignorer'])->name('clients.fusion.ignorer');
    Route::post('/clients/fusion/ignores/reset', [FusionController::class, 'resetIgnores'])->name('clients.fusion.ignores.reset');
});

use App\Http\Controllers\ClientController;
Route::middleware(['auth'])->group(function () {
    Route::resource('clients', ClientController::class);
});

use App\Http\Controllers\ContratController;
Route::resource('contrats', ContratController::class)->middleware('auth');
Route::middleware(['auth'])->group(function () {
    Route::post('/contrats/{contrat}/reimport-kizeo', [ContratController::class, 'reimportKizeo'])->name('contrats.reimport-kizeo');
    Route::post('/contrats/{contrat}/cloture', [ContratController::class, 'cloture'])->name('contrats.cloture');
    Route::patch('/contrats/{contrat}/renouvellement', [ContratController::class, 'updateRenouvellement'])->name('contrats.update-renouvellement');
});

use App\Http\Controllers\InterventionController;
Route::middleware(['auth'])->group(function () {
    Route::post('/periodes/traiter', [PeriodeAjustementController::class, 'traiter'])->name('periodes.traiter');
    Route::get('/interventions', [InterventionController::class, 'index'])->name('interventions.index');
    Route::get('/interventions/create', [InterventionController::class, 'create'])->name('interventions.create');
    Route::post('/interventions', [InterventionController::class, 'store'])->name('interventions.store');
    Route::get('/interventions/contrats-par-client/{client_nom}', [InterventionController::class, 'contratsPourClient'])->name('interventions.contrats-par-client');
    Route::get('/interventions/{intervention}', [InterventionController::class, 'show'])->name('interventions.show');
    Route::get('/interventions/{intervention}/edit', [InterventionController::class, 'edit'])->name('interventions.edit');
    Route::put('/interventions/{intervention}', [InterventionController::class, 'update'])->name('interventions.update');
    Route::delete('/interventions/{intervention}', [InterventionController::class, 'destroy'])->name('interventions.destroy');
    Route::post('/interventions/{intervention}/rattacher', [InterventionController::class, 'rattacher'])->name('interventions.rattacher');
});

use App\Http\Controllers\CorbeilleController;
Route::middleware(['auth', 'admin.only'])->group(function () {
    Route::get('/corbeille', [CorbeilleController::class, 'index'])->name('corbeille.index');
    Route::post('/corbeille/{id}/restaurer', [CorbeilleController::class, 'restaurer'])->name('corbeille.restaurer');
    Route::delete('/corbeille/{id}', [CorbeilleController::class, 'forceDelete'])->name('corbeille.force-delete');
});

use App\Http\Controllers\KizeoController;
Route::middleware(['auth'])->group(function () {
    Route::post('/kizeo/forcer', [KizeoController::class, 'forcer'])->name('kizeo.forcer');
    Route::post('/clients/{client}/reventiler', [ClientController::class, 'reventiler'])->name('clients.reventiler');
});

use App\Http\Controllers\PdfController;
Route::middleware(['auth'])->group(function () {
    Route::get('/contrats/{contrat}/pdf/choix', [PdfController::class, 'choix'])->name('contrats.pdf.choix');
    Route::get('/contrats/{contrat}/pdf', [PdfController::class, 'rapport'])->name('contrats.pdf');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/bitdefender', function () {
        return view('bitdefender.index');
    })->name('bitdefender.index');
});

use App\Http\Controllers\UserPreferenceController;
Route::middleware(['auth'])->group(function () {
    Route::post('/preferences', [UserPreferenceController::class, 'save'])->name('preferences.save');
    Route::get('/preferences/{page}', [UserPreferenceController::class, 'show'])->name('preferences.show');
});

use App\Http\Controllers\UserController;
Route::middleware(['auth', 'admin.only'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class);
});

use App\Http\Controllers\Admin\ClientsTableauController;
Route::middleware(['auth', 'admin.only'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/clients-tableau', [ClientsTableauController::class, 'index'])->name('clients-tableau.index');
    Route::post('/clients-tableau/{client}', [ClientsTableauController::class, 'update'])->name('clients-tableau.update');
});

use App\Http\Controllers\PerformanceController;
Route::middleware(['auth'])->group(function () {
    Route::get('/performance', [PerformanceController::class, 'index'])->name('performance.index');
});

use App\Http\Controllers\PortailController;
use App\Http\Controllers\PortailUserController;
Route::middleware(['auth', 'portail.only'])->group(function () {
    Route::get('/portail',               [PortailController::class, 'index'])->name('portail.index');
    Route::get('/portail/interventions', [PortailController::class, 'interventions'])->name('portail.interventions');
    Route::get('/portail/contrats',      [PortailController::class, 'contrats'])->name('portail.contrats');
    Route::get('/portail/documents',     [PortailController::class, 'documents'])->name('portail.documents');
});

Route::middleware(['auth', 'admin.only'])->group(function () {
    Route::post('/clients/{client}/portail-users', [PortailUserController::class, 'store'])->name('portail-users.store');
    Route::delete('/clients/{client}/portail-users/{user}', [PortailUserController::class, 'destroy'])->name('portail-users.destroy');
});

use App\Http\Controllers\CalculProrataController;
Route::get('/calculs/prorata', [CalculProrataController::class, 'index'])->name('calculs.prorata')->middleware('auth');

use App\Http\Controllers\ParametreController;
use App\Http\Controllers\MailTemplateController;
Route::middleware(['auth', 'admin.only'])->group(function () {
    Route::get('/parametres', [ParametreController::class, 'index'])->name('parametres.index');
    Route::post('/parametres', [ParametreController::class, 'update'])->name('parametres.update');
    Route::delete('/parametres/logo', [ParametreController::class, 'deleteLogo'])->name('parametres.logo.delete');
    Route::post('/parametres/motifs', [ParametreController::class, 'updateMotifs'])->name('parametres.motifs');
    Route::post('/parametres/techniciens', [ParametreController::class, 'updateTechniciens'])->name('parametres.techniciens');
    Route::post('/parametres/mail', [ParametreController::class, 'updateMail'])->name('parametres.mail.update');
    Route::post('/parametres/mail/test', [ParametreController::class, 'testSmtp'])->name('parametres.mail.test');

    Route::resource('mail-templates', MailTemplateController::class);
});

use App\Http\Controllers\BoiteIdeeController;
Route::middleware(['auth'])->group(function () {
    Route::get('/boite-idees', [BoiteIdeeController::class, 'index'])->name('boite-idees.index');
    Route::post('/boite-idees', [BoiteIdeeController::class, 'store'])->name('boite-idees.store');
    Route::post('/boite-idees/{boiteIdee}/vote', [BoiteIdeeController::class, 'vote'])->name('boite-idees.vote');
});
Route::middleware(['auth', 'admin.only'])->group(function () {
    Route::post('/boite-idees/{boiteIdee}/statut', [BoiteIdeeController::class, 'updateStatut'])->name('boite-idees.statut');
    Route::delete('/boite-idees/{boiteIdee}', [BoiteIdeeController::class, 'destroy'])->name('boite-idees.destroy');
});
