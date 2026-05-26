<?php
namespace App\Http\Controllers;
use App\Models\Contrat;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    // Page de choix de période
    public function choix(Contrat $contrat)
    {
        $contrat->load('client', 'interventions');
        $periodes = $contrat->getPeriodes()->filter(function($periode) {
            return $periode['debut']->lte(now());
        })->map(function($periode) use ($contrat) {
            $count = $contrat->interventions->filter(function($i) use ($periode) {
                return $i->deductible
                    && $i->date_intervention >= $periode['debut']
                    && $i->date_intervention <= $periode['fin'];
            })->count();
            return array_merge($periode, ['nb_interventions' => $count]);
        });

        $totalInterventions = $contrat->interventions->where('deductible', true)->count();
        return view('pdf.choix', compact('contrat', 'periodes', 'totalInterventions'));
    }

    // Génération du PDF
    public function rapport(Contrat $contrat, Request $request)
    {
        $contrat->load('client', 'interventions');
        $periodes = $contrat->getPeriodes();

        // Filtrer sur la période choisie si spécifiée
        $periodeChoisie = $request->get('periode'); // numéro de période ou 'toutes'

        $periodesAvecInterventions = $periodes->filter(function($periode) use ($periodeChoisie) {
            if (!$periode['debut']->lte(now())) return false;
            if ($periodeChoisie && $periodeChoisie !== 'toutes') {
                return $periode['numero'] == $periodeChoisie;
            }
            return true;
        })->map(function($periode) use ($contrat) {
            $interventions = $contrat->interventions->filter(function($i) use ($periode) {
                return $i->deductible
                    && $i->date_intervention >= $periode['debut']
                    && $i->date_intervention <= $periode['fin']
                    && $i->date_intervention->lte(now());
            })->sortBy('date_intervention');
            $mois = collect();
            $current = $periode['debut']->copy();
            while ($current->lte($periode['fin'])) {
                $moisInterventions = $interventions->filter(function($i) use ($current) {
                    return $i->date_intervention->month === $current->month
                        && $i->date_intervention->year === $current->year;
                });
                $mois->push([
                    'label'         => ucfirst($current->locale('fr')->isoFormat('MMMM YYYY')),
                    'interventions' => $moisInterventions,
                    'total_minutes' => $moisInterventions->sum('duree_minutes'),
                ]);
                $current->addMonth();
            }
            $totalMinutes   = $interventions->sum('duree_minutes');
            $heuresAllouees = $contrat->heures_par_periode;
            $diff           = ($heuresAllouees * 60) - $totalMinutes;
            $periodeFinie   = $periode['fin']->lt(now());
            return array_merge($periode, [
                'periode_finie'   => $periodeFinie,
                'mois'            => $mois,
                'total_minutes'   => $totalMinutes,
                'heures_allouees' => $heuresAllouees,
                'diff_minutes'    => $diff,
                'credit'          => $diff >= 0,
                'pct'             => $heuresAllouees > 0 ? min(100, round(($totalMinutes / ($heuresAllouees * 60)) * 100)) : 0,
            ]);
        });

        $pdf = Pdf::loadView('pdf.rapport', compact('contrat', 'periodesAvecInterventions'))
            ->setPaper('a4', 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => false, 'debugPng' => false]);
        $filename = 'rapport-' . $contrat->client->nom_societe . '-' . now()->format('Y-m-d') . '.pdf';
        if ($request->get('action') === 'download') {
            return $pdf->download($filename);
        }
        return $pdf->stream($filename);
    }
}
