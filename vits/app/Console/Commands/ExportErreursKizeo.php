<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExportErreursKizeo extends Command
{
    protected $signature   = 'kizeo:export-erreurs';
    protected $description = 'Extrait les erreurs/warnings Kizeo du log Laravel et les exporte en CSV';

    public function handle(): int
    {
        $logPath    = storage_path('logs/laravel.log');
        $outputPath = storage_path('logs/kizeo_erreurs.csv');

        if (! file_exists($logPath)) {
            $this->error("Fichier log introuvable : {$logPath}");
            return Command::FAILURE;
        }

        $logLines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $pattern  = '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(ERROR|WARNING): (.+)/i';
        $rows     = [];

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

            $rows[] = [$datetime, $level, $message];
        }

        $handle = fopen($outputPath, 'w');
        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8 pour Excel
        fputcsv($handle, ['date', 'niveau', 'message'], ';');
        foreach ($rows as $row) {
            fputcsv($handle, $row, ';');
        }
        fclose($handle);

        $count = count($rows);
        $this->info("{$count} ligne(s) exportée(s).");
        $this->line("  → {$outputPath}");

        return Command::SUCCESS;
    }
}
