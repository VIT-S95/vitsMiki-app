<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\KizeoService;

class SyncClientsKizeo extends Command
{
    protected $signature   = 'kizeo:sync-clients';
    protected $description = 'Synchronise les clients actifs de l\'app vers la liste Kizeo Liste-Societe';

    public function handle(KizeoService $kizeo): int
    {
        $this->info('Synchronisation clients → Kizeo...');
        $result = $kizeo->syncClientsVersKizeo();

        if ($result['success']) {
            $this->info('✅ ' . $result['message']);
        } else {
            $this->error('❌ ' . $result['message']);
            return 1;
        }

        return 0;
    }
}
