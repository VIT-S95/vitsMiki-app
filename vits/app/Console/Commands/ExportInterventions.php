<?php

namespace App\Console\Commands;

use App\Models\Intervention;
use Illuminate\Console\Command;

class ExportInterventions extends Command
{
    protected $signature   = 'kizeo:export-interventions {annee : Année à exporter (ex: 2025)}';
    protected $description = 'Exporte toutes les interventions d\'une année en CSV (séparateur ;, BOM UTF-8)';

    public function handle(): int
    {
        $annee = (int) $this->argument('annee');

        if ($annee < 2000 || $annee > 2100) {
            $this->error("Année invalide : {$annee}");
            return Command::FAILURE;
        }

        $interventions = Intervention::whereBetween('date_intervention', [
                "{$annee}-01-01",
                "{$annee}-12-31",
            ])
            ->orderBy('date_intervention')
            ->orderBy('heure_intervention')
            ->get([
                'date_intervention',
                'heure_intervention',
                'technicien',
                'client_nom',
                'type',
                'duree_minutes',
                'hors_contrat',
                'numero_bon_kizeo',
            ]);

        $outputPath = storage_path("logs/interventions_{$annee}.csv");
        $handle     = fopen($outputPath, 'w');

        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8 pour Excel
        fputcsv($handle, [
            'date', 'heure', 'technicien', 'client_nom',
            'type', 'duree_minutes', 'hors_contrat', 'numero_bon_kizeo',
        ], ';');

        foreach ($interventions as $i) {
            fputcsv($handle, [
                $i->date_intervention?->format('Y-m-d'),
                $i->heure_intervention,
                $i->technicien,
                $i->client_nom,
                $i->type,
                $i->duree_minutes,
                $i->hors_contrat ? 'oui' : 'non',
                $i->numero_bon_kizeo,
            ], ';');
        }

        fclose($handle);

        $count = $interventions->count();
        $this->info("{$count} intervention(s) exportée(s).");
        $this->line("  → {$outputPath}");

        return Command::SUCCESS;
    }
}
