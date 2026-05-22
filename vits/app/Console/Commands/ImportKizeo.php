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
        $this->info('Démarrage import Kizeo...');
        $result = $kizeo->importerInterventions();
        if ($result['success']) {
            $this->info('✅ ' . $result['message']);
        } else {
            $this->error('❌ ' . $result['message']);
        }
        return 0;
    }
}
