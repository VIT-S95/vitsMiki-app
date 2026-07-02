<?php

namespace App\Console\Commands;

use App\Models\Contrat;
use App\Models\Intervention;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RenouvelerContrats extends Command
{
    protected $signature = 'contrats:renouveler';

    protected $description = 'Renouvelle automatiquement les contrats arrivés à échéance';

    public function handle()
    {
        $hier = Carbon::yesterday()->toDateString();

        $contrats = Contrat::where('statut', 'en-cours')
            ->where('renouvellement_auto', true)
            ->whereDate('date_fin', $hier)
            ->get();

        foreach ($contrats as $contrat) {
            // Passer en expiré
            $contrat->update(['statut' => 'expire']);

            // Créer le nouveau contrat
            $newDateDebut = Carbon::parse($contrat->date_fin)->addDay();
            $newDateFin = $newDateDebut->copy()->addMonths($contrat->duree_mois)->subDay();
            $newNumero = $contrat->numero_renouvellement + 1;

            $nouveau = Contrat::create([
                'client_id' => $contrat->client_id,
                'titre' => $contrat->titre,
                'numero_contrat_vits' => $contrat->numero_contrat_vits,
                'date_debut' => $newDateDebut->toDateString(),
                'date_fin' => $newDateFin->toDateString(),
                'duree_mois' => $contrat->duree_mois,
                'duree_periode_mois' => $contrat->duree_periode_mois,
                'heures_par_periode' => $contrat->heures_par_periode,
                'numero_renouvellement' => $newNumero,
                'renouvellement_auto' => true,
                'notif_jours_avant' => $contrat->notif_jours_avant,
                'statut' => 'en-cours',
            ]);

            // Rattacher les interventions hors-contrat tombées dans le vide
            Intervention::where('client_nom', $contrat->client->nom_societe)
                ->where('hors_contrat', true)
                ->whereDate('date_intervention', '>=', $newDateDebut->toDateString())
                ->whereDate('date_intervention', '<=', $newDateFin->toDateString())
                ->update([
                    'contrat_id' => $nouveau->id,
                    'hors_contrat' => false,
                    'type_tri' => 'standard',
                ]);

            $this->info("✅ Contrat {$contrat->numero_contrat_vits} renouvelé → V{$newNumero}");
        }

        if ($contrats->isEmpty()) {
            $this->info('Aucun contrat à renouveler aujourd\'hui.');
        }

        return 0;
    }
}
