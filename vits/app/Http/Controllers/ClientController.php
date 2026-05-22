<?php
namespace App\Http\Controllers;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::query();
        if ($request->search) {
            $query->where('nom_societe', 'like', '%'.$request->search.'%');
        }
        $clients = $query->orderBy('nom_societe')->paginate(20);
        return view('clients.index', compact('clients'));
    }

    public function create() { return view('clients.create'); }

    public function store(Request $request)
    {
        $request->validate([
            'nom_societe' => 'required|string|max:255',
            'nom_signataire' => 'required|string|max:255',
            'email_signataire' => 'nullable|email',
            'numero_client_kizeo' => 'nullable|string|unique:clients',
            'numero_contrat_vits' => 'nullable|string|unique:clients',
        ]);
        Client::create($request->all());
        return redirect()->route('clients.index')->with('success', 'Client créé avec succès.');
    }

    public function show(Client $client)
    {
        $client->load('contrats');
        return view('clients.show', compact('client'));
    }

    public function edit(Client $client) { return view('clients.edit', compact('client')); }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'nom_societe' => 'required|string|max:255',
            'nom_signataire' => 'required|string|max:255',
            'email_signataire' => 'nullable|email',
            'numero_client_kizeo' => 'nullable|string|unique:clients,numero_client_kizeo,'.$client->id,
            'numero_contrat_vits' => 'nullable|string|unique:clients,numero_contrat_vits,'.$client->id,
        ]);
        $client->update($request->all());
        return redirect()->route('clients.show', $client)->with('success', 'Client mis à jour.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client supprimé.');
    }
}
