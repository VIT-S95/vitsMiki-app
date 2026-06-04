<?php
namespace App\Console\Commands;

use App\Models\Intervention;
use Illuminate\Console\Command;

class FixInterventionsAnterieures extends Command
{
    protected $signature   = 'vits:fix-interventions-anterieures';
    protected $description = 'Détache les interventions dont la date est antérieure au début du contrat auquel elles sont rattachées';

    public function handle(): int
    {
        $total   = 0;
        $fixees  = 0;
        $ignored = 0;

        Intervention::whereNotNull('contrat_id')
            ->with(['contrat.client'])
            ->chunkById(500, function ($interventions) use (&$total, &$fixees, &$ignored) {
                foreach ($interventions as $i) {
                    $total++;
                    $contrat = $i->contrat;
                    if (!$contrat || !$contrat->date_debut) continue;

                    if ($i->date_intervention->lt($contrat->date_debut)) {
                        // Exclure les interventions rattachées au client SNID (rattachement manuel voulu)
                        if ($contrat->client && $contrat->client->nom_societe === 'SNID') {
                            $ignored++;
                            continue;
                        }

                        $i->update([
                            'contrat_id'   => null,
                            'hors_contrat' => true,
                            'deductible'   => false,
                            'type_tri'     => 'hors-contrat',
                        ]);
                        $fixees++;
                    }
                }
            });

        $this->info("Interventions analysées : {$total}");
        $this->info("Corrigées              : {$fixees}");
        if ($ignored > 0) {
            $this->line("Ignorées (SNID)        : {$ignored}");
        }

        return Command::SUCCESS;
    }
}
