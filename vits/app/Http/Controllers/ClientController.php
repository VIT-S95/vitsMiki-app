<?php
namespace App\Http\Controllers;
use App\Models\Client;
use App\Services\KizeoService;
use Illuminate\Http\Request;
class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::with(['contrats' => function($q) {
            $q->where('statut', 'en-cours')->orderBy('date_debut', 'desc');
        }]);
        if ($request->search) {
            $query->where('nom_societe', 'like', '%'.$request->search.'%');
        }
        if ($request->get('sans_contrat') === '1') {
            $query->whereDoesntHave('contrats');
        } elseif (!$request->search) {
            $query->whereHas('contrats');
        }
        $sort = $request->get('sort', 'nom_societe');
        $dir  = $request->get('dir', 'asc');

        $allowedSorts = ['nom_societe', 'numero_client_kizeo', 'numero_contrat_vits', 'statut'];
        if (!in_array($sort, $allowedSorts)) $sort = 'nom_societe';
        if (!in_array($dir, ['asc', 'desc'])) $dir = 'asc';

        $clients = $query->orderBy($sort, $dir)->paginate((int)request('per_page', 20))->withQueryString();
        return view('clients.index', compact('clients', 'sort', 'dir'));
    }
    public function create() { return view('clients.create'); }
    public function store(Request $request)
    {
        $request->validate([
            'nom_societe'         => 'required|string|max:255',
            'nom_signataire'      => 'required|string|max:255',
            'email_signataire'    => 'nullable|email',
            'numero_client_kizeo' => 'nullable|string|unique:clients',
        ]);
        Client::create(array_merge(
            $request->only(['nom_societe','nom_signataire','email_signataire','numero_client_kizeo','numero_contrat_vits','statut']),
            [
                'mail_alerte_fin_contrat' => $request->boolean('mail_alerte_fin_contrat'),
                'mail_rapport_periodique' => $request->boolean('mail_rapport_periodique'),
                'mail_frequence'          => $request->input('mail_frequence', 'mensuel'),
            ]
        ));
        return redirect()->route('clients.index')->with('success', 'Client créé avec succès.');
    }
    public function show(Client $client)
    {
        $client->load(['contrats' => function($q) { $q->orderBy('date_debut','desc'); }, 'contrats.interventions', 'users']);
        return view('clients.show', compact('client'));
    }
    public function edit(Client $client) { return view('clients.edit', compact('client')); }
    public function update(Request $request, Client $client)
    {
        $request->validate([
            'nom_societe'         => 'required|string|max:255',
            'nom_signataire'      => 'required|string|max:255',
            'email_signataire'    => 'nullable|email',
            'numero_client_kizeo' => 'nullable|string|unique:clients,numero_client_kizeo,'.$client->id,
        ]);
        $client->update(array_merge(
            $request->only(['nom_societe','nom_signataire','email_signataire','numero_client_kizeo','statut']),
            [
                'mail_alerte_fin_contrat' => $request->boolean('mail_alerte_fin_contrat'),
                'mail_rapport_periodique' => $request->boolean('mail_rapport_periodique'),
                'mail_frequence'          => $request->input('mail_frequence', 'mensuel'),
            ]
        ));
        return redirect()->route('clients.show', $client)->with('success', 'Client modifié.');
    }
    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client supprimé.');
    }

    public function reventiler(Client $client, KizeoService $kizeo)
    {
        $result = $kizeo->reventilerInterventionsHorsContrat($client);
        return response()->json($result);
    }
}
