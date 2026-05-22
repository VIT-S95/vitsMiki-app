<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\Client;
use App\Models\Contrat;
use App\Models\Intervention;
use Carbon\Carbon;

class ImportWordpress extends Command
{
    protected $signature = 'import:wordpress';
    protected $description = 'Importe les donnees depuis la base WordPress';

    protected $users = [];
    protected $usermeta = [];
    protected $posts = [];
    protected $postmeta = [];

    public function handle()
    {
        $this->info('Chargement du fichier SQL...');
        $sql = file_get_contents('/workspaces/vitsMiki-app/sraza_gestion.sql');

        $this->info('Extraction des users...');
        preg_match_all("/\((\d+),\t'([^']*)',\t'[^']*',\t'[^']*',\t'([^']*)',\t'[^']*',\t'([^']*)',/", $sql, $m);
        foreach ($m[1] as $i => $id) {
            $this->users[$id] = ['email' => $m[3][$i], 'display_name' => '', 'login' => $m[2][$i]];
        }

        $this->info('Extraction des usermeta...');
        preg_match_all("/\(\d+,\t(\d+),\t'([^']+)',\t'([^']*)'\)/", $sql, $um);
        foreach ($um[1] as $i => $uid) {
            $key = $um[2][$i];
            $val = $um[3][$i];
            if ($key === 'display_name' && isset($this->users[$uid])) {
                $this->users[$uid]['display_name'] = $val;
            }
            if (!isset($this->usermeta[$uid])) $this->usermeta[$uid] = [];
            $this->usermeta[$uid][$key] = $val;
        }

        $this->info(count($this->users) . ' users charges');

        $this->info('Extraction des posts...');
        preg_match_all("/\((\d+),\t\d+,\t'[^']*',\t'[^']*',\t'[^']*',\t'([^']*)',\t'[^']*',\t'(publish|draft)',/", $sql, $pm);
        foreach ($pm[1] as $i => $id) {
            $this->posts[$id] = ['title' => $pm[2][$i], 'status' => $pm[3][$i]];
        }

        $this->info('Extraction des postmeta...');
        preg_match_all("/\((\d+),\t(\d+),\t'([^']+)',\t'([^']*)'\)/u", $sql, $pmu);
        foreach ($pmu[2] as $i => $pid) {
            $key = $pmu[3][$i];
            $val = $pmu[4][$i];
            if (!str_starts_with($key, '_')) {
                if (!isset($this->postmeta[$pid])) $this->postmeta[$pid] = [];
                $this->postmeta[$pid][$key] = $val;
            }
        }

        $this->info(count($this->postmeta) . ' postmeta charges');

        // Import clients
        $this->info('Import des clients...');
        $clientsImportes = 0;
        foreach ($this->users as $uid => $user) {
            $caps = $this->usermeta[$uid]['vitswp_capabilities'] ?? '';
            if (!str_contains($caps, 'client')) continue;

            $meta = $this->usermeta[$uid] ?? [];
            $societe = $meta['societe'] ?? $meta['first_name'] ?? $user['display_name'] ?? 'Client ' . $uid;

            Client::updateOrCreate(
                ['numero_client_kizeo' => (string)$uid],
                [
                    'nom_societe'      => $societe ?: ('Client ' . $uid),
                    'nom_signataire'   => $user['display_name'] ?: $user['login'],
                    'email_signataire' => $user['email'],
                    'statut'           => 'actif',
                ]
            );
            $clientsImportes++;
        }
        $this->info($clientsImportes . ' clients importes');

        // Import contrats
        $this->info('Import des contrats...');
        $contratsImportes = 0;
        $postSql = $sql;
        preg_match_all("/\((\d+),\t\d+,\t'([^']*)',\t'[^']*',\t'[^']*',\t'([^']*)',\t'[^']*',\t'publish',.*?'contract'/s", $postSql, $cp);

        // Méthode alternative - chercher directement les contrats
        $lines = explode("\n", $sql);
        foreach ($lines as $line) {
            if (!str_contains($line, "'contract'")) continue;
            preg_match("/\((\d+),/", $line, $idm);
            if (!isset($idm[1])) continue;
            $pid = $idm[1];
            $meta = $this->postmeta[$pid] ?? [];
            if (empty($meta)) continue;

            $clientWpId = $meta['client'] ?? null;
            if (!$clientWpId) continue;

            $client = Client::where('numero_client_kizeo', (string)$clientWpId)->first();
            if (!$client) continue;

            $dateDebut = $this->parseDate($meta['date_de_debut'] ?? null);
            $dateFin   = $this->parseDate($meta['date_de_fin'] ?? null);
            $duree     = (int)($meta['duree_contrat'] ?? 12);
            $heures    = (int)($meta['nombre_heure_allouees'] ?? 10);
            $periodes  = (int)($meta['periodes'] ?? 12);
            $numero    = $meta['contrat_id_vits'] ?? 'WP-' . $pid;

            $statut = 'non-actif';
            if ($dateDebut && $dateFin) {
                $d = Carbon::parse($dateDebut);
                $f = Carbon::parse($dateFin);
                if (now()->between($d, $f)) $statut = 'en-cours';
                elseif (now()->gt($f)) $statut = 'expire';
            }

            Contrat::updateOrCreate(
                ['numero_contrat_vits' => $numero],
                [
                    'client_id'           => $client->id,
                    'titre'               => 'Contrat maintenance',
                    'numero_contrat_vits' => $numero,
                    'date_debut'          => $dateDebut,
                    'date_fin'            => $dateFin,
                    'duree_mois'          => in_array($duree, [12,24,36]) ? $duree : 12,
                    'duree_periode_mois'  => in_array($periodes, [1,3,6,12]) ? $periodes : 12,
                    'heures_par_periode'  => $heures ?: 10,
                    'statut'              => $statut,
                    'numero_renouvellement' => 0,
                ]
            );
            $contratsImportes++;
        }
        $this->info($contratsImportes . ' contrats importes');

        // Import interventions
        $this->info('Import des interventions...');
        $interventionsImportees = 0;
        foreach ($lines as $line) {
            if (!str_contains($line, "'intervention'")) continue;
            preg_match("/\((\d+),/", $line, $idm);
            if (!isset($idm[1])) continue;
            $pid = $idm[1];
            $meta = $this->postmeta[$pid] ?? [];
            if (empty($meta)) continue;

            $wpContratId = $meta['contrat_de_maintenance'] ?? null;
            if (!$wpContratId) continue;

            $wpNumero = $this->postmeta[$wpContratId]['contrat_id_vits'] ?? 'WP-' . $wpContratId;
            $contrat = Contrat::where('numero_contrat_vits', $wpNumero)->first();
            if (!$contrat) continue;

            $date = $this->parseDate($meta['date'] ?? null);
            if (!$date) continue;

            $bonNumero = 'WP-' . $pid;
            if (Intervention::where('numero_bon_kizeo', $bonNumero)->exists()) continue;

            $forfait = (int)($meta['forfait_temps'] ?? 0);
            $estFlash = ($meta['forfait'] ?? '1') === '0';

            Intervention::create([
                'contrat_id'        => $contrat->id,
                'date_intervention' => $date,
                'numero_bon_kizeo'  => $bonNumero,
                'type'              => $estFlash ? 'flash' : 'site',
                'duree_minutes'     => $forfait,
                'statut'            => 'traitee',
                'type_tri'          => 'standard',
                'source_kizeo'      => false,
            ]);
            $interventionsImportees++;
        }
        $this->info($interventionsImportees . ' interventions importees');
        $this->info('Import termine !');
    }

    protected function parseDate($value)
    {
        if (!$value || strlen($value) < 6) return null;
        try {
            if (strlen($value) === 8 && is_numeric($value)) {
                return Carbon::createFromFormat('Ymd', $value)->toDateString();
            }
            return Carbon::parse($value)->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }
}