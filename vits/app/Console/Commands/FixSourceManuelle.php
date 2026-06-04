<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixSourceManuelle extends Command
{
    protected $signature   = 'vits:fix-source-manuelle';
    protected $description = 'Informe sur la colonne source_manuelle (historique non récupérable)';

    public function handle(): int
    {
        $this->info('source_manuelle : informations');
        $this->line('');
        $this->line('La colonne source_manuelle est valorisée à true lors de l\'import Kizeo');
        $this->line('quand le champ client = "-" et que autre_client est renseigné.');
        $this->line('');
        $this->warn('L\'historique antérieur à l\'ajout de cette colonne ne peut pas être');
        $this->warn('rétroactivement corrigé car l\'information était perdue lors de l\'import.');
        $this->line('');
        $this->info('Seules les interventions importées après cette migration sont taggées.');
        return Command::SUCCESS;
    }
}
