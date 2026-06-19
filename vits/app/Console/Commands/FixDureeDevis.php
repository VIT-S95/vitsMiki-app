<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixDureeDevis extends Command
{
    protected $signature   = 'vits:fix-duree-devis';
    protected $description = 'Informe sur la colonne duree_devis_minutes (historique non récupérable)';

    public function handle(): int
    {
        $this->info('duree_devis_minutes : informations');
        $this->line('');
        $this->line('La colonne duree_devis_minutes est calculée à partir du champ temps_du_devis');
        $this->line('du formulaire Kizeo "interventions sur site" (45252), lors de l\'import.');
        $this->line('');
        $this->warn('L\'historique antérieur à l\'ajout de cette colonne ne peut pas être');
        $this->warn('rétroactivement corrigé car le champ temps_du_devis n\'était pas stocké en base.');
        $this->line('');
        $this->info('Seules les interventions importées après cette migration sont valorisées.');
        return Command::SUCCESS;
    }
}
