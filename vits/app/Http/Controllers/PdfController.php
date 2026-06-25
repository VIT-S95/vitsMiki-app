<?php
namespace App\Http\Controllers;
use App\Models\Contrat;
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

        $totalInterventions = $contrat->interventions->where('deductible', true)->filter(function($i) use ($contrat) { return $i->date_intervention >= $contrat->date_debut; })->count();
        return view('pdf.choix', compact('contrat', 'periodes', 'totalInterventions'));
    }

    // Génération du PDF
    public function rapport(Contrat $contrat, Request $request)
    {
        $contrat->load('client', 'interventions');
        $periodes = $contrat->getPeriodes();

        // Filtrer sur la période choisie si spécifiée
        $periodeChoisie = $request->get('periode'); // numéro de période ou 'toutes'

        $interventionsAnterieures = $contrat->interventions
            ->filter(fn($i) => $i->date_intervention < $contrat->date_debut)
            ->sortBy('date_intervention')->values();
        $minutesAnterieures = $interventionsAnterieures->where('deductible', true)->sum('duree_minutes');

        $isFirstPeriode = true;
        $periodesAvecInterventions = $periodes->filter(function($periode) use ($periodeChoisie) {
            if (!$periode['debut']->lte(now())) return false;
            if ($periodeChoisie && $periodeChoisie !== 'toutes') {
                return $periode['numero'] == $periodeChoisie;
            }
            return true;
        })->map(function($periode) use ($contrat, $minutesAnterieures, &$isFirstPeriode) {
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
                    'is_future'     => $current->copy()->startOfMonth()->gt(now()->copy()->startOfMonth()),
                ]);
                $current->addMonth();
            }
            $totalMinutes = $interventions->sum('duree_minutes');
            if ($isFirstPeriode) {
                $totalMinutes  += $minutesAnterieures;
                $isFirstPeriode = false;
            }
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
                'pct'             => $heuresAllouees > 0 ? round(($totalMinutes / ($heuresAllouees * 60)) * 100) : 0,
            ]);
        });

        $filename = 'Rapport de consommation - ' . $contrat->client->nom_societe . ' - ' . now()->format('d/m/Y') . '.pdf';

        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_top'    => 15,
            'margin_bottom' => 15,
            'margin_left'   => 15,
            'margin_right'  => 15,
        ]);
        $mpdf->SetHTMLFooter('
            <table width="100%" style="border-top: 0.5px solid #D3D1C7; padding-top: 4px;">
                <tr>
                    <td style="font-size: 9px; color: #888; text-align: left;">VIT-S — Document confidentiel — usage client uniquement</td>
                    <td style="font-size: 9px; color: #888; text-align: right;">Page {PAGENO}/{nbpg}</td>
                </tr>
            </table>
        ');
        $html = view('pdf.rapport', compact('contrat', 'periodesAvecInterventions', 'interventionsAnterieures'))->render();
        $mpdf->WriteHTML($html);

        if ($request->get('action') === 'download') {
            return response($mpdf->Output($filename, 'S'))
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        }
        return response($mpdf->Output($filename, 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }
}
