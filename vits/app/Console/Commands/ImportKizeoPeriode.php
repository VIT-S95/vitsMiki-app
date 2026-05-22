<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Services\KizeoService;

class ImportKizeoPeriode extends Command
{
    protected $signature = 'kizeo:import-periode {debut} {fin}';
    protected $description = 'Importe les interventions Kizeo entre deux dates (YYYY-MM-DD)';

    public function handle()
    {
        $debut = $this->argument('debut');
        $fin   = $this->argument('fin');
        $this->info("Import Kizeo du {$debut} au {$fin}...");
        $service = new KizeoService();
        $result  = $service->importerParPeriode($debut, $fin);
        $this->info($result['message']);
        if ($result['errors'] > 0) {
            $this->warn("{$result['errors']} erreur(s)");
        }
    }
}
