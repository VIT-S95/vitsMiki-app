<?php

namespace App\Console\Commands;

use App\Models\Intervention;
use Illuminate\Console\Command;

class FixClientNom extends Command
{
    protected $signature   = 'vits:fix-client-nom';
    protected $description = 'Renseigne client_nom sur les interventions avec contrat qui ont ce champ vide';

    public function handle(): int
    {
        $total = Intervention::whereNull('client_nom')
            ->whereNotNull('contrat_id')
            ->count();

        if ($total === 0) {
            $this->info('Aucune intervention à corriger.');
            return Command::SUCCESS;
        }

        $this->info("{$total} intervention(s) à corriger…");
        $updated = 0;

        Intervention::whereNull('client_nom')
            ->whereNotNull('contrat_id')
            ->with('contrat.client')
            ->chunk(500, function ($interventions) use (&$updated) {
                foreach ($interventions as $i) {
                    $nom = $i->contrat?->client?->nom_societe;
                    if ($nom) {
                        $i->update(['client_nom' => $nom]);
                        $updated++;
                    }
                }
            });

        $this->info("{$updated} intervention(s) mises à jour.");
        return Command::SUCCESS;
    }
}
