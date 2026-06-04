<?php
namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Intervention;
use Illuminate\Http\Request;

class FusionController extends Controller
{
    public function index()
    {
        $saisiesManuelles = Intervention::where('source_manuelle', true)
            ->selectRaw('client_nom, COUNT(*) as nb_interventions, SUM(duree_minutes) as total_minutes')
            ->groupBy('client_nom')
            ->orderBy('client_nom')
            ->get();

        $clients = Client::orderBy('nom_societe')->get();

        return view('clients.fusion', compact('saisiesManuelles', 'clients'));
    }

    public function fusionner(Request $request)
    {
        $request->validate([
            'client_nom_source' => 'required|string|max:255',
            'client_id_cible'   => 'required|exists:clients,id',
        ]);

        $clientCible = Client::findOrFail($request->client_id_cible);

        $updated = Intervention::where('client_nom', $request->client_nom_source)
            ->where('source_manuelle', true)
            ->update([
                'client_nom'      => $clientCible->nom_societe,
                'source_manuelle' => false,
            ]);

        return redirect()->route('clients.fusion')
            ->with('success', "{$updated} intervention(s) fusionnée(s) vers « {$clientCible->nom_societe} ».");
    }
}
