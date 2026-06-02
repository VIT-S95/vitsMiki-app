<?php

namespace App\Console\Commands;

use App\Services\KizeoService;
use Illuminate\Console\Command;

class ReimportKizeo extends Command
{
    protected $signature   = 'kizeo:reimport {date_debut : Date début (YYYY-MM-DD)} {date_fin : Date fin (YYYY-MM-DD)}';
    protected $description = 'Réimporte les interventions Kizeo sur une période donnée';

    public function handle(KizeoService $kizeo): int
    {
        $dateDebut = $this->argument('date_debut');
        $dateFin   = $this->argument('date_fin');

        $this->info("Réimport Kizeo : {$dateDebut} → {$dateFin}");

        $result = $kizeo->importerParPeriode($dateDebut, $dateFin);

        if (! $result['success']) {
            $this->error($result['message']);
            return Command::FAILURE;
        }

        $this->line("  Importées    : <fg=green>{$result['imported']}</>");
        $this->line("  Doublons     : {$result['skipped']}");
        $this->line("  Sans contrat : {$result['sans_contrat']}");
        $this->line("  Erreurs      : <fg=" . ($result['errors'] > 0 ? 'red' : 'default') . ">{$result['errors']}</>");
        $this->info($result['message']);

        return Command::SUCCESS;
    }
}
