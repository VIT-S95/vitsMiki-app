<?php

namespace App\Http\Controllers;

use App\Models\Intervention;

class PortailController extends Controller
{
    public function index()
    {
        $client = auth()->user()->client;
        $client->load(['contrats.interventions']);

        $contratActif = $client->contrats->where('statut', 'en-cours')->sortByDesc('date_debut')->first();

        $minutesConsommees = 0;
        $minutesAllouees   = 0;
        $currentPeriode    = null;

        if ($contratActif) {
            $minutesAllouees = $contratActif->heures_par_periode * 60;
            foreach ($contratActif->getPeriodes() as $periode) {
                if (now()->between($periode['debut'], $periode['fin'])) {
                    $currentPeriode = $periode;
                    break;
                }
            }
            if ($currentPeriode) {
                $minutesConsommees = $contratActif->interventions
                    ->where('deductible', true)
                    ->filter(fn($i) => $i->date_intervention >= $currentPeriode['debut']
                                    && $i->date_intervention <= $currentPeriode['fin'])
                    ->sum('duree_minutes');
            }
        }

        $contratIds = $client->contrats->pluck('id');
        $derniereIntervention = $contratIds->isNotEmpty()
            ? Intervention::whereIn('contrat_id', $contratIds)->orderByDesc('date_intervention')->first()
            : null;

        return view('portail.index', compact(
            'client', 'contratActif', 'minutesConsommees', 'minutesAllouees',
            'currentPeriode', 'derniereIntervention'
        ));
    }

    public function interventions()
    {
        $client = auth()->user()->client;

        $interventions = Intervention::where(function ($q) use ($client) {
                $q->whereHas('contrat', fn($q2) => $q2->where('client_id', $client->id));
            })
            ->orWhere(function ($q) use ($client) {
                $q->where('client_nom', $client->nom_societe)->where('hors_contrat', true);
            })
            ->with('contrat')
            ->orderByDesc('date_intervention')
            ->paginate(25);

        return view('portail.interventions', compact('client', 'interventions'));
    }

    public function contrats()
    {
        $client = auth()->user()->client;
        $client->load(['contrats' => fn($q) => $q->orderByDesc('date_debut'), 'contrats.interventions']);

        return view('portail.contrats', compact('client'));
    }

    public function documents()
    {
        $client = auth()->user()->client;
        $client->load(['contrats' => fn($q) => $q->orderByDesc('date_debut'), 'contrats.interventions']);

        return view('portail.documents', compact('client'));
    }
}
