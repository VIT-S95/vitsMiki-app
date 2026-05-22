<?php
namespace App\Http\Controllers;
use App\Models\Contrat;
use App\Models\Client;
use Illuminate\Http\Request;

class ContratController extends Controller
{
    public function index(Request $request)
    {
        $query = Contrat::with('client');
        if ($request->search) {
            $query->whereHas('client', function($q) use ($request) {
                $q->where('nom_societe', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->statut) {
            $query->where('statut', $request->statut);
        }
        $contrats = $query->orderBy('date_fin')->paginate(20);
        return view('contrats.index', compact('contrats'));
    }

    public function create()
    {
        $clients = Client::whereDoesntHave('contrats', function($q) {
            $q->where('statut', 'en-cours');
        })->orderBy('nom_societe')->get();
        return view('contrats.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id'          => 'required|exists:clients,id',
            'date_debut'         => 'required|date',
            'duree_mois'         => 'required|integer|in:12,24,36',
            'heures_par_periode' => 'required|integer|min:1',
            'duree_periode_mois' => 'required|integer|in:1,3,6,12',
        ]);

        $data = $request->all();
        $data['date_fin'] = \Carbon\Carbon::parse($request->date_debut)
            ->addMonths((int)$request->duree_mois)
            ->subDay();

        $client = Client::find($request->client_id);
        $data['titre'] = 'Contrat maintenance ' . now()->year;
        $data['numero_renouvellement'] = $client->contrats()->count();
        $data['numero_contrat_vits'] = $client->numero_contrat_vits;

        Contrat::create($data);
        return redirect()->route('contrats.index')->with('success', 'Contrat créé avec succès.');
    }

    public function show(Contrat $contrat)
    {
        $contrat->load('client', 'interventions');
        $periodes = $contrat->getPeriodes();
        return view('contrats.show', compact('contrat', 'periodes'));
    }

    public function edit(Contrat $contrat)
    {
        $clients = Client::orderBy('nom_societe')->get();
        return view('contrats.edit', compact('contrat', 'clients'));
    }

    public function update(Request $request, Contrat $contrat)
    {
        $request->validate([
            'date_debut'         => 'required|date',
            'duree_mois'         => 'required|integer|in:12,24,36',
            'heures_par_periode' => 'required|integer|min:1',
            'duree_periode_mois' => 'required|integer|in:1,3,6,12',
            'statut'             => 'required|in:non-actif,en-cours,expire',
        ]);

        $data = $request->all();
        $data['date_fin'] = \Carbon\Carbon::parse($request->date_debut)
            ->addMonths((int)$request->duree_mois)
            ->subDay();

        $contrat->update($data);
        return redirect()->route('contrats.show', $contrat)->with('success', 'Contrat mis à jour.');
    }

    public function destroy(Contrat $contrat)
    {
        $contrat->delete();
        return redirect()->route('contrats.index')->with('success', 'Contrat supprimé.');
    }
}
