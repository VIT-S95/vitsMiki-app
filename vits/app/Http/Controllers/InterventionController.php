<?php
namespace App\Http\Controllers;
use App\Models\Intervention;
use App\Models\Contrat;
use Illuminate\Http\Request;

class InterventionController extends Controller
{
    public function index(Request $request)
    {
        $query = Intervention::with(['contrat.client'])
            ->orderBy('date_intervention', 'desc')
            ->orderBy('heure_intervention', 'desc');

        if ($request->search) {
            $query->whereHas('contrat.client', function($q) use ($request) {
                $q->where('nom_societe', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->deductible !== null && $request->deductible !== '') {
            $query->where('deductible', $request->deductible === '1');
        }

        $interventions = $query->paginate((int)request('per_page', 20))->withQueryString();
        return view('interventions.index', compact('interventions'));
    }

        public function create(Request $request)
    {
        $contrat = Contrat::with('client')->findOrFail($request->contrat_id);
        $motifs = config('vits.motifs_intervention', [
            'Dépassement de quota n-1',
            'Report période précédente',
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
            'type'              => 'required|in:site,distance,flash,administrateur',
            'motif'             => 'nullable|string|max:255',
            'notes'             => 'nullable|string',
        ]);

        $dureeMinutes = (int)$request->input('duree_minutes', 0);

        $data = [
            'contrat_id'        => $request->contrat_id,
            'date_intervention' => $request->date_intervention,
            'numero_bon_kizeo'  => $request->numero_bon_kizeo ?: null,
            'type'              => $request->type,
            'duree_minutes'     => $dureeMinutes,
            'motif'             => $request->motif ?: null,
            'notes'             => $request->notes ?: null,
            'statut'            => 'traitee',
            'type_tri'          => $request->boolean('deductible') ? 'standard' : 'hors-contrat',
            'deductible'        => $request->boolean('deductible'),
            'source_kizeo'      => false,
            'flash_numero'      => null,
            'flash_consomme'    => false,
        ];

        if ($request->type === 'flash') {
            $flashNumero = (int)$request->input('flash_numero', 1);
            $data['flash_numero']   = $flashNumero;
            $data['flash_consomme'] = ($flashNumero === 3);
            $data['duree_minutes']  = ($flashNumero === 3) ? 20 : 0;
            $data['deductible']     = true;
            $data['type_tri']       = 'standard';
        }

        $intervention = Intervention::create($data);

        // Recalculer les flash si nécessaire
        if ($request->type === 'flash') {
            Intervention::recalculerFlash($request->contrat_id, $request->date_intervention);
        }

        return redirect()->route('contrats.show', $request->contrat_id)
            ->with('success', 'Intervention ajoutée avec succès.');
    }

    public function edit(Intervention $intervention)
    {
        $intervention->load('contrat.client');
        $motifs = config('vits.motifs_intervention', [
            'Dépassement de quota n-1',
            'Report période précédente',
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
            'type'              => 'required|in:site,distance,flash,administrateur',
            'motif'             => 'nullable|string|max:255',
            'notes'             => 'nullable|string',
        ]);

        $dureeMinutes = (int)$request->input('duree_minutes', 0);

        $data = [
            'date_intervention' => $request->date_intervention,
            'numero_bon_kizeo'  => $request->numero_bon_kizeo ?: null,
            'type'              => $request->type,
            'duree_minutes'     => $dureeMinutes,
            'motif'             => $request->motif ?: null,
            'notes'             => $request->notes ?: null,
            'type_tri'          => $request->boolean('deductible') ? 'standard' : 'hors-contrat',
            'deductible'        => $request->boolean('deductible'),
        ];

        if ($request->type === 'flash') {
            $flashNumero = (int)$request->input('flash_numero', 1);
            $data['flash_numero']   = $flashNumero;
            $data['flash_consomme'] = ($flashNumero === 3);
            $data['duree_minutes']  = ($flashNumero === 3) ? 20 : 0;
            $data['deductible']     = true;
        }

        $intervention->update($data);

        if ($request->type === 'flash') {
            Intervention::recalculerFlash($intervention->contrat_id, $request->date_intervention);
        }

        return redirect()->route('contrats.show', $intervention->contrat_id)
            ->with('success', 'Intervention modifiée.');
    }

    public function destroy(Intervention $intervention)
    {
        $contratId = $intervention->contrat_id;
        $estFlash  = $intervention->type === 'flash';
        $date      = $intervention->date_intervention;
        $intervention->delete();
        if ($estFlash) {
            Intervention::recalculerFlash($contratId, $date);
        }
        return redirect()->route('contrats.show', $contratId)
            ->with('success', 'Intervention supprimée.');
    }
}
