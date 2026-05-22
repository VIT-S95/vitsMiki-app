<?php
namespace App\Http\Controllers;
use App\Models\Contrat;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
    public function rapport(Contrat $contrat)
    {
        $contrat->load('client', 'interventions');
        $periodes = $contrat->getPeriodes();

        $periodesAvecInterventions = $periodes->map(function($periode) use ($contrat) {
            $interventions = $contrat->interventions->filter(function($i) use ($periode) {
                return $i->date_intervention >= $periode['debut']
                    && $i->date_intervention <= $periode['fin'];
            })->sortBy('date_intervention');

            $mois = collect();
            $current = $periode['debut']->copy();
            while ($current->lte($periode['fin'])) {
                $moisInterventions = $interventions->filter(function($i) use ($current) {
                    return $i->date_intervention->month === $current->month
                        && $i->date_intervention->year === $current->year;
                });
                $mois->push([
                    'label'         => ucfirst($current->isoFormat('MMMM YYYY')),
                    'interventions' => $moisInterventions,
                    'total_minutes' => $moisInterventions->sum('duree_minutes'),
                ]);
                $current->addMonth();
            }

            $totalMinutes = $interventions->sum('duree_minutes');
            $heuresAllouees = $contrat->heures_par_periode;
            $diff = ($heuresAllouees * 60) - $totalMinutes;

            return array_merge($periode, [
                'mois'           => $mois,
                'total_minutes'  => $totalMinutes,
                'heures_allouees'=> $heuresAllouees,
                'diff_minutes'   => $diff,
                'credit'         => $diff >= 0,
                'pct'            => $heuresAllouees > 0 ? min(100, round(($totalMinutes / ($heuresAllouees * 60)) * 100)) : 0,
            ]);
        });

        $pdf = Pdf::loadView('pdf.rapport', compact('contrat', 'periodesAvecInterventions'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('rapport-' . $contrat->client->nom_societe . '-' . now()->format('Y-m-d') . '.pdf');
    }
}
