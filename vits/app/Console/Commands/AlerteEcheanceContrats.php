<?php

namespace App\Console\Commands;

use App\Models\Contrat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class AlerteEcheanceContrats extends Command
{
    protected $signature = 'contrats:alertes';

    protected $description = 'Vérifie les contrats proches de l\'échéance et met à jour le badge';

    public function handle()
    {
        $contrats = Contrat::where('statut', 'en-cours')
            ->whereNotNull('notif_jours_avant')
            ->get()
            ->filter(function ($contrat) {
                $joursRestants = (int) now()->diffInDays($contrat->date_fin, false);

                return $joursRestants >= 0 && $joursRestants <= $contrat->notif_jours_avant;
            });

        Cache::put('contrats_alertes_count', $contrats->count(), now()->addDay());
        $this->info("{$contrats->count()} contrat(s) proche(s) de l'échéance.");

        return 0;
    }
}
