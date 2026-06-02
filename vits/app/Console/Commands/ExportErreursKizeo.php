<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExportErreursKizeo extends Command
{
    protected $signature   = 'kizeo:export-erreurs';
    protected $description = 'Extrait les erreurs/warnings Kizeo du log Laravel et les exporte groupés';

    public function handle(): int
    {
        $logPath    = storage_path('logs/laravel.log');
        $outputPath = storage_path('logs/kizeo_erreurs.txt');

        if (! file_exists($logPath)) {
            $this->error("Fichier log introuvable : {$logPath}");
            return Command::FAILURE;
        }

        $logLines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $pattern  = '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(ERROR|WARNING): (.+)/i';
        $groups   = [];

        foreach ($logLines as $line) {
            if (stripos($line, 'kizeo') === false) {
                continue;
            }
            if (! preg_match($pattern, $line, $m)) {
                continue;
            }

            [, $datetime, $level, $message] = $m;

            // Supprimer le contexte JSON en fin de ligne
            $message = trim(preg_replace('/\s*\{.*$/s', '', $message));

            // Clé de regroupement : normaliser les valeurs variables
            $key = preg_replace('/\b\d{5,}\b/', '{N}', $message);   // n° de bons, IDs
            $key = preg_replace("/'[^']+'/", "'{X}'", $key);         // noms clients entre guillemets
            $key = preg_replace('/\d{4}-\d{2}-\d{2}/', '{DATE}', $key); // dates

            $groups[$key][] = [
                'datetime' => $datetime,
                'level'    => $level,
                'message'  => $message,
            ];
        }

        // Trier par nombre d'occurrences décroissant
        uasort($groups, fn($a, $b) => count($b) - count($a));

        $totalLignes = array_sum(array_map('count', $groups));
        $totalTypes  = count($groups);

        $out   = [];
        $out[] = str_repeat('=', 60);
        $out[] = 'EXPORT ERREURS KIZEO';
        $out[] = 'Généré le : ' . now()->format('d/m/Y à H:i:s');
        $out[] = "Source    : {$logPath}";
        $out[] = "Résultat  : {$totalLignes} ligne(s) — {$totalTypes} type(s) distincts";
        $out[] = str_repeat('=', 60);

        if (empty($groups)) {
            $out[] = '';
            $out[] = 'Aucune ligne ERROR ou WARNING contenant "Kizeo" trouvée.';
        }

        foreach ($groups as $key => $occurrences) {
            $count  = count($occurrences);
            $levels = implode('/', array_unique(array_column($occurrences, 'level')));
            $out[]  = '';
            $out[]  = "[{$levels}] {$count}x — {$key}";
            $out[]  = str_repeat('-', 50);
            foreach ($occurrences as $occ) {
                $out[] = "  {$occ['datetime']}  {$occ['message']}";
            }
        }

        $out[] = '';

        file_put_contents($outputPath, implode("\n", $out));

        $this->info("Export terminé : {$totalLignes} ligne(s) en {$totalTypes} type(s).");
        $this->line("  → {$outputPath}");

        return Command::SUCCESS;
    }
}
