<?php
namespace App\Http\Controllers;
use App\Models\Client;
use App\Models\Contrat;
use App\Models\Intervention;
use App\Models\PeriodeAjustement;
use App\Services\KizeoService;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(KizeoService $kizeo)
    {
        $seuilEcheanceMois = (int) config('vits.seuil_echeance_mois', 3);
        $seuilHeuresPct    = (int) config('vits.seuil_heures_pct', 80);
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

        // Périodes terminées avec solde non traité
        $periodesARegler = collect();
        $contratActifs = Contrat::with(['client', 'interventions'])
            ->where('statut', 'en-cours')->get();
        foreach ($contratActifs as $contrat) {
            $periodes = $contrat->getPeriodes();
            foreach ($periodes as $periode) {
                // Seulement les périodes terminées
                if ($periode['fin']->gt(now())) continue;
                // Vérifier si déjà traité
                $dejaTraite = PeriodeAjustement::where('contrat_id', $contrat->id)
                    ->where('periode_numero', $periode['numero'])->exists();
                if ($dejaTraite) continue;
                // Calculer le solde
                $minutesConsommees = $contrat->interventions
                    ->filter(function($i) use ($periode) {
                        return $i->deductible
                            && $i->date_intervention >= $periode['debut']
                            && $i->date_intervention <= $periode['fin'];
                    })->sum('duree_minutes');
                $solde = ($contrat->heures_par_periode * 60) - $minutesConsommees;
                if ($solde == 0) continue; // Pile poil, rien à faire
                $periodesARegler->push([
                    'contrat'        => $contrat,
                    'periode'        => $periode,
                    'solde_minutes'  => $solde,
                    'credit'         => $solde > 0,
                    'h'              => abs((int)floor($solde / 60)),
                    'm'              => abs($solde % 60),
                ]);
            }
        }

        $totalAlertes = $contratsExpires->count() + $contratsEcheance->count()
            + $interventionsNonTraitees->count() + $contratsBrouillon
            + $clientsSansInfos + $clientsSansContrat + $contratsDepassement->count()
            + $periodesARegler->count();

        $derniereImportRaw = Cache::get('kizeo_derniere_import');
        $derniereImport    = $derniereImportRaw ? \Carbon\Carbon::parse($derniereImportRaw) : null;
        $kizeoLog       = Cache::get('kizeo_import_log');
        $nonLus         = Cache::remember('kizeo_non_lus', 300, fn() => $kizeo->getNombreNonLus());

        return view('dashboard', compact(
            'totalClients', 'totalContrats', 'interventionsMois', 'totalAlertes',
            'contratsExpires', 'contratsEcheance', 'interventionsNonTraitees',
            'contratsBrouillon', 'clientsSansInfos', 'clientsSansContrat',
            'contratsDepassement', 'periodesARegler',
            'derniereImport', 'kizeoLog', 'nonLus'
        ));
    }
}
