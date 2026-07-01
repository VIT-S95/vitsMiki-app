<?php
namespace App\Http\Controllers;
use App\Models\Intervention;
use App\Models\Contrat;
use Illuminate\Http\Request;

class InterventionController extends Controller
{
    public function index(Request $request)
    {
        $techniciens = Intervention::whereNotNull('technicien')
            ->distinct()->orderBy('technicien')->pluck('technicien');

        $query = Intervention::with(['contrat.client'])
            ->orderBy('date_intervention', 'desc')
            ->orderBy('heure_intervention', 'desc');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('client_nom', 'like', '%'.$request->search.'%')
                  ->orWhereHas('contrat.client', function($q2) use ($request) {
                      $q2->where('nom_societe', 'like', '%'.$request->search.'%');
                  });
            });
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->deductible !== null && $request->deductible !== '') {
            $query->where('deductible', $request->deductible === '1');
        }
        $query->when($request->technicien, fn($q) => $q->where('technicien', $request->technicien));

        match ($request->periode) {
            'today'    => $query->whereDate('date_intervention', today()),
            'week'     => $query->whereBetween('date_intervention', [now()->startOfWeek(), now()->endOfWeek()]),
            'month'    => $query->whereBetween('date_intervention', [now()->startOfMonth(), now()->endOfMonth()]),
            'semestre' => $query->where('date_intervention', '>=', now()->subMonths(6)->toDateString()),
            'annee'    => $query->whereBetween('date_intervention', [now()->startOfYear(), now()->endOfYear()]),
            'n1'       => $query->whereBetween('date_intervention', [
                              now()->subYear()->startOfYear()->toDateString(),
                              now()->subYear()->endOfYear()->toDateString(),
                          ]),
            'custom'   => $query
                              ->when($request->date_debut, fn($q) => $q->where('date_intervention', '>=', $request->date_debut))
                              ->when($request->date_fin,   fn($q) => $q->where('date_intervention', '<=', $request->date_fin)),
            default    => null,
        };

        $interventions  = $query->paginate((int)request('per_page', 20))->withQueryString();
        $contratsActifs = Contrat::with('client')->where('statut', 'en-cours')
            ->orderBy('client_id')->get();
        return view('interventions.index', compact('interventions', 'techniciens', 'contratsActifs'));
    }

        public function create(Request $request)
    {
        $contrat = Contrat::with('client')->findOrFail($request->contrat_id);
        return view('interventions.create', compact('contrat'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'contrat_id'        => 'required|exists:contrats,id',
            'date_intervention' => 'required|date',
            'type'              => 'required|in:site,distance,flash,administrateur',
        ]);

        $dureeMinutes = (int)$request->input('duree_minutes', 0);

        $data = [
            'contrat_id'        => $request->contrat_id,
            'date_intervention' => $request->date_intervention,
            'numero_bon_kizeo'  => $request->numero_bon_kizeo ?: null,
            'type'              => $request->type,
            'duree_minutes'     => $dureeMinutes,
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

    public function show(Intervention $intervention)
    {
        $intervention->load('contrat.client');
        return view('interventions.show', compact('intervention'));
    }

    public function edit(Intervention $intervention)
    {
        $intervention->load('contrat.client');
        $contratsActifs = Contrat::with('client')->where('statut', 'en-cours')
            ->orderBy('client_id')->get();
        return view('interventions.edit', compact('intervention', 'contratsActifs'));
    }

    public function update(Request $request, Intervention $intervention)
    {
        $request->validate([
            'date_intervention' => 'required|date',
            'type'              => 'required|in:site,distance,flash,ajustement',
            'contrat_id'        => 'nullable|exists:contrats,id',
        ]);

        $data = $request->only([
            'date_intervention', 'heure_intervention', 'technicien', 'client_nom',
            'type', 'donneur_ordre', 'n_ticket', 'n_devis', 'commentaires',
        ]);
        $data['contrat_id']        = $request->contrat_id ?: null;
        $data['duree_minutes']     = (int)$request->input('duree_minutes', 0);
        $data['deductible']        = $request->boolean('deductible');
        $data['hors_heure_ouvree'] = $request->boolean('hors_heure_ouvree');
        $data['type_tri']          = $data['deductible'] ? 'standard' : 'hors-contrat';

        $intervention->update($data);

        $redirect = $intervention->contrat_id
            ? redirect()->route('contrats.show', $intervention->contrat_id)
            : redirect()->route('interventions.index');
        return $redirect->with('success', 'Intervention modifiée.');
    }

    public function rattacher(Request $request, Intervention $intervention)
    {
        $request->validate(['contrat_id' => 'required|exists:contrats,id']);
        $intervention->update([
            'contrat_id'   => $request->contrat_id,
            'hors_contrat' => false,
            'deductible'   => true,
            'type_tri'     => 'standard',
        ]);
        return redirect()->route('interventions.index')->with('success', 'Intervention rattachée au contrat.');
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
        $redirect = $contratId
            ? redirect()->route('contrats.show', $contratId)
            : redirect()->route('interventions.index');
        return $redirect->with('success', 'Intervention supprimée.');
    }
}
