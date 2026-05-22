<?php
namespace App\Http\Controllers;
use App\Models\Client;
use App\Models\Contrat;
use App\Models\Intervention;

class DashboardController extends Controller
{
    public function index()
    {
        $seuilEcheanceMois = config('vits.seuil_echeance_mois', 3);
        $seuilHeuresPct    = config('vits.seuil_heures_pct', 80);

        $totalClients      = Client::where('statut', 'actif')->count();
        $totalContrats     = Contrat::where('statut', 'en-cours')->count();
        $interventionsMois = Intervention::whereMonth('date_intervention', now()->month)
                                ->whereYear('date_intervention', now()->year)->count();

        $contratsExpires = Contrat::with('client')
            ->where('statut', 'en-cours')
            ->whereNotNull('date_fin')
            ->whereDate('date_fin', '<', now())
            ->get();

        $contratsEcheance = Contrat::with('client')
            ->where('statut', 'en-cours')
            ->whereNotNull('date_fin')
            ->whereDate('date_fin', '>=', now())
            ->whereDate('date_fin', '<=', now()->addMonths($seuilEcheanceMois))
            ->get();

        $interventionsNonTraitees = Intervention::with('contrat.client')
            ->where('statut', 'non-traitee')
            ->latest('date_intervention')
            ->take(5)->get();

        $contratsBrouillon  = Contrat::where('statut', 'non-actif')->count();
        $clientsSansContrat = Client::where('statut', 'actif')->whereDoesntHave('contrats')->count();
        $clientsSansInfos   = Client::where('statut', 'actif')
            ->where(function($q) {
                $q->whereNull('nom_signataire')->orWhere('nom_signataire', '');
            })->count();

        $contratsDepassement = Contrat::with('client')
            ->where('statut', 'en-cours')->get()
            ->filter(function($contrat) use ($seuilHeuresPct) {
                $heuresAllouees = $contrat->heures_par_periode * 60;
                if ($heuresAllouees == 0) return false;
                $heuresConsommees = $contrat->interventions()
                    ->whereMonth('date_intervention', now()->month)
                    ->sum('duree_minutes');
                return ($heuresConsommees / $heuresAllouees * 100) >= $seuilHeuresPct;
            });

        $totalAlertes = $contratsExpires->count() + $contratsEcheance->count()
            + $interventionsNonTraitees->count() + $contratsBrouillon
            + $clientsSansInfos + $clientsSansContrat + $contratsDepassement->count();

        return view('dashboard', compact(
            'totalClients', 'totalContrats', 'interventionsMois', 'totalAlertes',
            'contratsExpires', 'contratsEcheance', 'interventionsNonTraitees',
            'contratsBrouillon', 'clientsSansInfos', 'clientsSansContrat', 'contratsDepassement'
        ));
    }
}
