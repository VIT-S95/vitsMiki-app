<?php
namespace App\Http\Controllers;
use App\Models\Contrat;
use App\Models\Intervention;
use App\Models\PeriodeAjustement;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PeriodeAjustementController extends Controller
{
    public function traiter(Request $request)
    {
        $request->validate([
            'contrat_id'     => 'required|exists:contrats,id',
            'periode_numero' => 'required|integer',
            'periode_fin'    => 'required|date',
            'solde_minutes'  => 'required|integer',
            'action'         => 'required|in:reporte,ignore',
        ]);

        $contrat = Contrat::with('interventions')->findOrFail($request->contrat_id);

        // Enregistrer la décision
        PeriodeAjustement::create([
            'contrat_id'     => $contrat->id,
            'periode_numero' => $request->periode_numero,
            'periode_fin'    => $request->periode_fin,
            'solde_minutes'  => $request->solde_minutes,
            'action'         => $request->action,
        ]);

        if ($request->action === 'reporte') {
            $solde = (int)$request->solde_minutes;
            $periodeFin = Carbon::parse($request->periode_fin);
            $dateReport = $periodeFin->copy()->addDay(); // 1er jour période suivante

            if ($solde > 0) {
                // Crédit — heures restantes non consommées
                $h = floor($solde / 60);
                $m = $solde % 60;
                $label = "Report crédit P{$request->periode_numero} (+{$h}h".($m>0?$m.'min':'').')';
                Intervention::create([
                    'contrat_id'        => $contrat->id,
                    'date_intervention' => $dateReport,
                    'type'      => 'ajustement',
                    'technicien' => 'Système',
                    'duree_minutes'     => -$solde, // négatif = crédit
                    'commentaires'      => $label,
                    'statut'            => 'traitee',
                    'type_tri'          => 'standard',
                    'deductible'        => true,
                    'source_kizeo'      => false,
                ]);
            } else {
                // Dépassement — heures consommées en trop
                $abs = abs($solde);
                $h = floor($abs / 60);
                $m = $abs % 60;
                $label = "Dépassement Quota P{$request->periode_numero} ({$h}h".($m>0?$m.'min':'').')';
                Intervention::create([
                    'contrat_id'        => $contrat->id,
                    'date_intervention' => $dateReport,
                    'type'      => 'ajustement',
                    'technicien' => 'Système',
                    'duree_minutes'     => $abs, // positif = débit
                    'commentaires'      => $label,
                    'statut'            => 'traitee',
                    'type_tri'          => 'standard',
                    'deductible'        => true,
                    'source_kizeo'      => false,
                ]);
            }
        }

        return redirect()->route('dashboard')
            ->with('success', $request->action === 'reporte' ? 'Ajustement reporté sur la période suivante.' : 'Période ignorée.');
    }
}
