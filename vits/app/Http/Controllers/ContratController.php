<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Contrat;
use App\Services\KizeoService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ContratController extends Controller
{
    public function index(Request $request)
    {
        $query = Contrat::with(['client', 'interventions'])
            ->join('clients', 'contrats.client_id', '=', 'clients.id')
            ->select('contrats.*');

        if ($request->search) {
            $query->where('clients.nom_societe', 'like', '%'.$request->search.'%');
        }
        // Par défaut : rediriger vers en-cours
        if (! $request->has('statut')) {
            return redirect()->route('contrats.index', ['statut' => 'en-cours']);
        }
        $statut = $request->get('statut');
        if ($statut && $statut !== 'tous') {
            $query->where('contrats.statut', $statut);
        }

        $sort = $request->get('sort', 'date_fin');
        $dir = $request->get('dir', 'asc');

        $allowedSorts = ['date_fin', 'heures_par_periode', 'duree_mois', 'duree_periode_mois', 'statut', 'nom_societe'];
        if (! in_array($sort, $allowedSorts)) {
            $sort = 'date_fin';
        }
        if (! in_array($dir, ['asc', 'desc'])) {
            $dir = 'asc';
        }

        if ($sort === 'nom_societe') {
            $query->orderBy('clients.nom_societe', $dir);
        } else {
            $query->orderBy('contrats.'.$sort, $dir);
        }

        $contrats = $query->paginate((int) request('per_page', 20))->withQueryString();

        return view('contrats.index', compact('contrats', 'sort', 'dir'));
    }

    public function create()
    {
        $clients = Client::orderBy('nom_societe')->get();

        return view('contrats.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'numero_contrat_vits' => 'required|string',
            'date_debut' => 'required|date',
            'duree_mois' => 'required|in:12,24,36',
            'duree_periode_mois' => 'required|in:1,3,6,12',
            'heures_par_periode' => 'required|integer|min:1',
        ]);
        $data = $request->all();
        $data['date_fin'] = Carbon::parse($data['date_debut'])->addMonths((int) $data['duree_mois'])->toDateString();
        $data['statut'] = 'en-cours';
        $data['numero_renouvellement'] = 0;
        $contrat = Contrat::create($data);
        $client = Client::find($data['client_id']);
        if ($client) {
            app(KizeoService::class)->reventilerInterventionsHorsContrat($client);
        }

        return redirect()->route('contrats.index')->with('success', 'Contrat créé.');
    }

    public function show(Contrat $contrat)
    {
        $contrat->load(['client', 'interventions' => function ($q) {
            $q->orderBy('date_intervention', 'asc');
        }]);
        $periodes = $contrat->getPeriodes();
        $interventionsAnterieures = $contrat->interventions()
            ->where('date_intervention', '<', $contrat->date_debut)
            ->orderBy('date_intervention')
            ->get();

        return view('contrats.show', compact('contrat', 'periodes', 'interventionsAnterieures'));
    }

    public function edit(Contrat $contrat)
    {
        $clients = Client::orderBy('nom_societe')->get();

        return view('contrats.edit', compact('contrat', 'clients'));
    }

    public function update(Request $request, Contrat $contrat)
    {
        $request->validate([
            'numero_contrat_vits' => 'required|string|unique:contrats,numero_contrat_vits,'.$contrat->id,
            'date_debut' => 'required|date',
            'duree_mois' => 'required|in:12,24,36',
            'duree_periode_mois' => 'required|in:1,3,6,12',
            'heures_par_periode' => 'required|integer|min:1',
        ]);
        $data = $request->all();
        $data['date_fin'] = Carbon::parse($data['date_debut'])->addMonths((int) $data['duree_mois'])->toDateString();
        $contrat->update($data);
        app(KizeoService::class)->reventilerInterventionsHorsContrat($contrat->client);

        return redirect()->route('contrats.show', $contrat)->with('success', 'Contrat modifié.');
    }

    public function destroy(Contrat $contrat)
    {
        $contrat->delete();

        return redirect()->route('contrats.index')->with('success', 'Contrat supprimé.');
    }

    public function updateRenouvellement(Request $request, Contrat $contrat)
    {
        $contrat->update([
            'renouvellement_auto' => $request->boolean('renouvellement_auto'),
            'notif_jours_avant' => (int) $request->input('notif_jours_avant', 30),
        ]);

        return redirect()->back()->with('success', 'Paramètres de renouvellement mis à jour.');
    }

    public function reimportKizeo(Contrat $contrat, KizeoService $kizeo)
    {
        $result = $kizeo->importerParPeriode(
            $contrat->date_debut->format('Y-m-d'),
            now()->format('Y-m-d')
        );

        $msg = $result['success']
            ? "Réimport terminé : {$result['message']}"
            : "Erreur réimport : {$result['message']}";

        return redirect()->back()->with('success', $msg);
    }
}
