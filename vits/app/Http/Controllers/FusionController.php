<?php
namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Intervention;
use App\Models\Setting;
use Illuminate\Http\Request;

class FusionController extends Controller
{
    public function index()
    {
        $ignores = json_decode(Setting::get('fusion_ignores', '[]'), true) ?? [];

        $saisiesManuelles = Intervention::where('source_manuelle', true)
            ->selectRaw('client_nom, COUNT(*) as nb_interventions, SUM(duree_minutes) as total_minutes')
            ->groupBy('client_nom')
            ->orderBy('client_nom')
            ->get()
            ->filter(fn($s) => !in_array($s->client_nom, $ignores))
            ->values();

        $clients  = Client::orderBy('nom_societe')->get();
        $hasIgnores = !empty($ignores);

        return view('clients.fusion', compact('saisiesManuelles', 'clients', 'hasIgnores'));
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

    public function ignorer(Request $request)
    {
        $request->validate(['client_nom_source' => 'required|string|max:255']);

        $ignores   = json_decode(Setting::get('fusion_ignores', '[]'), true) ?? [];
        $nom       = $request->client_nom_source;
        if (!in_array($nom, $ignores)) {
            $ignores[] = $nom;
        }
        Setting::set('fusion_ignores', json_encode(array_values($ignores)));

        return redirect()->route('clients.fusion')
            ->with('success', "« {$nom} » masqué. Cliquez sur « Réafficher les ignorés » pour le restaurer.");
    }

    public function resetIgnores()
    {
        Setting::set('fusion_ignores', '[]');
        return redirect()->route('clients.fusion')->with('success', 'Liste des ignorés réinitialisée.');
    }
}
