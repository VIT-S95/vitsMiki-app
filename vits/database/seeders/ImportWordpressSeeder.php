<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Client;
use App\Models\Contrat;
use App\Models\Intervention;
use Carbon\Carbon;

class ImportWordpressSeeder extends Seeder
{
    protected $wpDb;

    public function run(): void
    {
        $this->wpDb = DB::connection('wordpress');
        $this->command->info('Import WordPress -> VIT-S');
        $this->importClients();
        $this->importContrats();
        $this->importInterventions();
        $this->command->info('Import termine !');
    }

    protected function importClients()
    {
        $this->command->info('Import clients...');
        $users = $this->wpDb->table('vitswp_users')
            ->join('vitswp_usermeta', 'vitswp_users.ID', '=', 'vitswp_usermeta.user_id')
            ->where('vitswp_usermeta.meta_key', 'vitswp_capabilities')
            ->where('vitswp_usermeta.meta_value', 'like', '%client%')
            ->select('vitswp_users.*')
            ->get();

        foreach ($users as $user) {
            $meta = $this->getMeta('vitswp_usermeta', 'user_id', $user->ID);
            Client::updateOrCreate(
                ['numero_client_kizeo' => (string)$user->ID],
                [
                    'nom_societe'         => $meta['societe'] ?? $user->display_name,
                    'nom_signataire'      => $user->display_name,
                    'email_signataire'    => $user->user_email,
                    'numero_contrat_vits' => $meta['numero_contrat'] ?? null,
                    'statut'              => 'actif',
                ]
            );
        }
        $this->command->info(count($users) . ' clients importes');
    }

    protected function importContrats()
    {
        $this->command->info('Import contrats...');
        $posts = $this->wpDb->table('vitswp_posts')
            ->where('post_type', 'contract')
            ->where('post_status', 'publish')
            ->get();

        $count = 0;
        foreach ($posts as $post) {
            $meta = $this->getMeta('vitswp_postmeta', 'post_id', $post->ID);
            $clientWpId = $meta['client'] ?? null;
            if (!$clientWpId) continue;

            $client = Client::where('numero_client_kizeo', (string)$clientWpId)->first();
            if (!$client) continue;

            $dateDebut = $this->parseDate($meta['date_de_debut'] ?? null);
            $dateFin   = $this->parseDate($meta['date_de_fin'] ?? null);
            $duree     = (int)($meta['duree_contrat'] ?? 12);
            $heures    = (int)($meta['nombre_heure_allouees'] ?? 10);
            $periodes  = (int)($meta['periodes'] ?? 12);

            $statut = 'non-actif';
            if ($dateDebut && $dateFin) {
                if (now()->between($dateDebut, $dateFin)) $statut = 'en-cours';
                elseif (now()->gt($dateFin)) $statut = 'expire';
            }

            Contrat::updateOrCreate(
                ['numero_contrat_vits' => $meta['contrat_id_vits'] ?? 'WP-'.$post->ID],
                [
                    'client_id'            => $client->id,
                    'titre'                => $post->post_title,
                    'numero_contrat_vits'  => $meta['contrat_id_vits'] ?? 'WP-'.$post->ID,
                    'date_debut'           => $dateDebut,
                    'date_fin'             => $dateFin,
                    'duree_mois'           => $duree,
                    'duree_periode_mois'   => $periodes,
                    'heures_par_periode'   => $heures,
                    'statut'               => $statut,
                    'numero_renouvellement'=> 0,
                ]
            );
            $count++;
        }
        $this->command->info($count . ' contrats importes');
    }

    protected function importInterventions()
    {
        $this->command->info('Import interventions...');
        $posts = $this->wpDb->table('vitswp_posts')
            ->where('post_type', 'intervention')
            ->where('post_status', 'publish')
            ->get();

        $count = 0;
        foreach ($posts as $post) {
            $meta = $this->getMeta('vitswp_postmeta', 'post_id', $post->ID);
            $wpContratId = $meta['contrat_de_maintenance'] ?? null;
            if (!$wpContratId) continue;

            $wpContrat = $this->wpDb->table('vitswp_postmeta')
                ->where('post_id', $wpContratId)
                ->where('meta_key', 'contrat_id_vits')
                ->value('meta_value');

            $contrat = $wpContrat
                ? Contrat::where('numero_contrat_vits', $wpContrat)->first()
                : null;

            if (!$contrat) continue;

            $date = $this->parseDate($meta['date'] ?? null);
            if (!$date) continue;

            $forfaitTemps = (int)($meta['forfait_temps'] ?? 0);
            $estFlash     = ($meta['forfait'] ?? '1') === '0';
            $type         = $estFlash ? 'flash' : 'site';

            if (!Intervention::where('numero_bon_kizeo', 'WP-'.$post->ID)->exists()) {
                Intervention::create([
                    'contrat_id'        => $contrat->id,
                    'date_intervention' => $date,
                    'numero_bon_kizeo'  => 'WP-'.$post->ID,
                    'type'              => $type,
                    'duree_minutes'     => $forfaitTemps,
                    'statut'            => 'traitee',
                    'type_tri'          => 'standard',
                    'source_kizeo'      => false,
                ]);
                $count++;
            }
        }
        $this->command->info($count . ' interventions importees');
    }

    protected function getMeta($table, $column, $id)
    {
        $rows = $this->wpDb->table($table)->where($column, $id)->get();
        $meta = [];
        foreach ($rows as $row) {
            if (!str_starts_with($row->meta_key, '_')) {
                $meta[$row->meta_key] = $row->meta_value;
            }
        }
        return $meta;
    }

    protected function parseDate($value)
    {
        if (!$value) return null;
        try {
            if (strlen($value) === 8) {
                return Carbon::createFromFormat('Ymd', $value)->toDateString();
            }
            return Carbon::parse($value)->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }
}
