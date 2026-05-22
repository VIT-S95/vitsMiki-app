<?php
namespace App\Http\Controllers;
use App\Models\Intervention;
use App\Models\Contrat;
use Illuminate\Http\Request;

class InterventionController extends Controller
{
    public function create(Request $request)
    {
        $contrat = Contrat::with('client')->findOrFail($request->contrat_id);
        $motifs = config('vits.motifs_intervention', [
            'Dépassement de quota n-1',
            'Intervention proactive',
            'Mise à jour planifiée',
            'Urgence hors contrat',
            'Prestation complémentaire',
        ]);
        return view('interventions.create', compact('contrat', 'motifs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'contrat_id'        => 'required|exists:contrats,id',
            'date_intervention' => 'required|date',
            'type'              => 'required|in:site,distance,flash',
            'motif'             => 'nullable|string',
        ]);

        $data = $request->all();
        $data['source_kizeo'] = false;

        if ($request->type === 'flash') {
            $data['duree_minutes'] = 0;
            $data['flash_numero'] = 1;
            $data['flash_consomme'] = false;
        } else {
            $heures = intval($request->input('heures', 0));
            $minutes = intval($request->input('minutes', 0));
            $data['duree_minutes'] = ($heures * 60) + $minutes;
        }

        $intervention = Intervention::create($data);

        if ($request->type === 'flash') {
            Intervention::recalculerFlash($request->contrat_id, $request->date_intervention);
        }

        return redirect()->route('contrats.show', $request->contrat_id)
            ->with('success', 'Intervention ajoutée avec succès.');
    }

    public function edit(Intervention $intervention)
    {
        $motifs = config('vits.motifs_intervention', [
            'Dépassement de quota n-1',
            'Intervention proactive',
            'Mise à jour planifiée',
            'Urgence hors contrat',
            'Prestation complémentaire',
        ]);
        return view('interventions.edit', compact('intervention', 'motifs'));
    }

    public function update(Request $request, Intervention $intervention)
    {
        $request->validate([
            'date_intervention' => 'required|date',
            'type'              => 'required|in:site,distance,flash',
        ]);

        $data = $request->all();

        if ($request->type !== 'flash') {
            $heures = intval($request->input('heures', 0));
            $minutes = intval($request->input('minutes', 0));
            $data['duree_minutes'] = ($heures * 60) + $minutes;
        }

        $intervention->update($data);

        if ($request->type === 'flash') {
            Intervention::recalculerFlash($intervention->contrat_id, $intervention->date_intervention);
        }

        return redirect()->route('contrats.show', $intervention->contrat_id)
            ->with('success', 'Intervention mise à jour.');
    }

    public function destroy(Intervention $intervention)
    {
        $contrat_id = $intervention->contrat_id;
        $type = $intervention->type;
        $intervention->delete();

        if ($type === 'flash') {
            Intervention::recalculerFlash($contrat_id, now());
        }

        return redirect()->route('contrats.show', $contrat_id)
            ->with('success', 'Intervention supprimée.');
    }
}
