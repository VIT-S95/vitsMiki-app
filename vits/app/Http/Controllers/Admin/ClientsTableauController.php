<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientsTableauController extends Controller
{
    public function index()
    {
        $clients = Client::orderBy('nom_societe')->get();

        return view('admin.clients-tableau', compact('clients'));
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'nom_societe'             => 'required|string|max:255',
            'email_signataire'        => 'nullable|email|max:255',
            'nom_signataire'          => 'nullable|string|max:255',
            'numero_client_kizeo'     => 'nullable|string|max:255|unique:clients,numero_client_kizeo,' . $client->id,
            'statut'                  => 'required|in:actif,sans_contrat,inactif',
            'mail_frequence'          => 'required|in:mensuel,hebdo,trimestriel',
        ]);

        $client->update([
            'nom_societe'             => $data['nom_societe'],
            'email_signataire'        => $data['email_signataire'] ?? null,
            'nom_signataire'          => $data['nom_signataire'] ?? null,
            'numero_client_kizeo'     => $data['numero_client_kizeo'] ?? null,
            'statut'                  => $data['statut'],
            'mail_alerte_fin_contrat' => $request->boolean('mail_alerte_fin_contrat'),
            'mail_rapport_periodique' => $request->boolean('mail_rapport_periodique'),
            'mail_frequence'          => $data['mail_frequence'],
        ]);

        return response()->json([
            'success' => true,
            'message' => "Client « {$client->nom_societe} » mis à jour.",
        ]);
    }
}
