<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Services\KizeoService;

class ImportKizeo extends Command
{
    protected $signature   = 'kizeo:import';
    protected $description = 'Importe les interventions depuis Kizeo Forms';

    public function handle(KizeoService $kizeo)
    {
        $this->info('Démarrage import Kizeo (depuis dernière intervention en base)...');
        $result = $kizeo->importDepuisDerniereIntervention();
        if ($result['success']) {
            $this->info('✅ ' . $result['message']);
            if (($result['skipped'] ?? 0) > 0) {
                $this->line("   ↩ {$result['skipped']} doublon(s) ignoré(s)");
            }
            if ($result['errors'] > 0) {
                $this->warn("⚠ {$result['errors']} erreur(s)");
            }
        } else {
            $this->error('❌ ' . $result['message']);
        }
        return 0;
    }
}
