<?php
require __DIR__.'/vits/vendor/autoload.php';
$app = require_once __DIR__.'/vits/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Client;
use App\Models\Contrat;
use App\Models\Intervention;
use Carbon\Carbon;

echo "Lecture ligne par ligne...\n";

$file = fopen('/workspaces/vitsMiki-app/sraza_gestion.sql', 'r');
$clientIds = [];
$usermeta  = [];
$users     = [];
$postmeta  = [];
$contractIds     = [];
$interventionIds = [];
$n = 0;

while (!feof($file)) {
    $line = fgets($file, 16384);
    $n++;
    if ($n % 100000 === 0) echo "Ligne $n...\n";

    // -- USERS
    if (str_contains($line, 'INSERT INTO `vitswp_users`')) {
        // Format: (ID, 'login', 'pass', 'nicename', 'email', ...
        preg_match_all("/\((\d+),\s+'([^']*)',\s+'[^']*',\s+'[^']*',\s+'([^']*)'/", $line, $m);
        foreach ($m[1] as $i => $id) {
            $users[$id] = ['login' => $m[2][$i], 'email' => $m[3][$i], 'display_name' => ''];
        }
    }

    // -- USERMETA (tabs entre colonnes)
    if (str_contains($line, "'vitswp_capabilities'")) {
        // Format: (meta_id, user_id, 'key', 'value'),
        preg_match_all("/\(\d+,\s+(\d+),\s+'vitswp_capabilities',\s+'([^']*)'\)/", $line, $um);
        foreach ($um[1] as $i => $uid) {
            $val = $um[2][$i];
            if (str_contains($val, 'client')) {
                $clientIds[] = $uid;
            }
        }
    }

    // Toutes les usermeta utiles (display_name, societe)
    if (str_contains($line, 'INSERT INTO `vitswp_usermeta`') ||
        (str_contains($line, "'display_name'") || str_contains($line, "'societe'"))) {
        preg_match_all("/\(\d+,\s+(\d+),\s+'(display_name|societe)',\s+'([^']*)'\)/", $line, $um2);
        foreach ($um2[1] as $i => $uid) {
            if (!isset($usermeta[$uid])) $usermeta[$uid] = [];
            $usermeta[$uid][$um2[2][$i]] = $um2[3][$i];
        }
    }

    // -- POSTS contrats
    if (str_contains($line, "'contract'") && str_contains($line, "'publish'")) {
        preg_match_all("/\((\d+),\s+\d+/", $line, $pm);
        foreach ($pm[1] as $id) $contractIds[] = $id;
    }

    // -- POSTS interventions
    if (str_contains($line, "'intervention'") && str_contains($line, "'publish'")) {
        preg_match_all("/\((\d+),\s+\d+/", $line, $pm);
        foreach ($pm[1] as $id) $interventionIds[] = $id;
    }

    // -- POSTMETA
    if (str_contains($line, 'INSERT INTO `vitswp_postmeta`') ||
        str_contains($line, "'client'") || str_contains($line, "'date_de_debut'") ||
        str_contains($line, "'date_de_fin'") || str_contains($line, "'duree_contrat'") ||
        str_contains($line, "'nombre_heure_allouees'") || str_contains($line, "'periodes'") ||
        str_contains($line, "'contrat_id_vits'") || str_contains($line, "'contrat_de_maintenance'") ||
        str_contains($line, "'date'") || str_contains($line, "'forfait_temps'") ||
        str_contains($line, "'forfait'")) {
        preg_match_all("/\(\d+,\s+(\d+),\s+'([^']+)',\s+'([^']*)'\)/u", $line, $pmu);
        foreach ($pmu[1] as $i => $pid) {
            $key = $pmu[2][$i];
            $val = $pmu[3][$i];
            if (!str_starts_with($key, '_')) {
                if (!isset($postmeta[$pid])) $postmeta[$pid] = [];
                $postmeta[$pid][$key] = $val;
            }
        }
    }
}
fclose($file);

$clientIds = array_unique($clientIds);
$contractIds = array_unique($contractIds);
$interventionIds = array_unique($interventionIds);

echo count($clientIds) . " clients, " . count($contractIds) . " contrats, " . count($interventionIds) . " interventions\n";

// Import clients
echo "Import clients...\n";
$c = 0;
foreach ($clientIds as $uid) {
    $meta = $usermeta[$uid] ?? [];
    $user = $users[$uid] ?? [];
    $societe = $meta['societe'] ?? $user['display_name'] ?? ('Client '.$uid);
    if (empty(trim($societe))) $societe = 'Client '.$uid;
    Client::updateOrCreate(
        ['numero_client_kizeo' => (string)$uid],
        ['nom_societe' => $societe, 'nom_signataire' => $meta['display_name'] ?? $user['login'] ?? '', 'email_signataire' => $user['email'] ?? '', 'statut' => 'actif']
    );
    $c++;
}
echo "$c clients importes\n";

// Import contrats
echo "Import contrats...\n";
$c = 0;
foreach ($contractIds as $pid) {
    $meta = $postmeta[$pid] ?? [];
    if (empty($meta)) continue;
    $clientWpId = $meta['client'] ?? null;
    if (!$clientWpId) continue;
    $client = Client::where('numero_client_kizeo', (string)$clientWpId)->first();
    if (!$client) continue;
    $dateDebut = parseDate($meta['date_de_debut'] ?? null);
    $dateFin   = parseDate($meta['date_de_fin'] ?? null);
    $duree     = (int)($meta['duree_contrat'] ?? 12);
    $heures    = (int)($meta['nombre_heure_allouees'] ?? 10);
    $periodes  = (int)($meta['periodes'] ?? 12);
    $numero    = $meta['contrat_id_vits'] ?? 'WP-'.$pid;
    $statut = 'non-actif';
    if ($dateDebut && $dateFin) {
        $d = Carbon::parse($dateDebut); $f = Carbon::parse($dateFin);
        if (now()->between($d,$f)) $statut = 'en-cours';
        elseif (now()->gt($f)) $statut = 'expire';
    }
    Contrat::updateOrCreate(
        ['numero_contrat_vits' => $numero],
        ['client_id' => $client->id, 'titre' => 'Contrat maintenance', 'numero_contrat_vits' => $numero,
         'date_debut' => $dateDebut, 'date_fin' => $dateFin,
         'duree_mois' => in_array($duree,[12,24,36])?$duree:12,
         'duree_periode_mois' => in_array($periodes,[1,3,6,12])?$periodes:12,
         'heures_par_periode' => $heures?:10, 'statut' => $statut, 'numero_renouvellement' => 0]
    );
    $c++;
}
echo "$c contrats importes\n";

// Import interventions
echo "Import interventions...\n";
$c = 0;
foreach ($interventionIds as $pid) {
    $meta = $postmeta[$pid] ?? [];
    if (empty($meta)) continue;
    $wpContratId = $meta['contrat_de_maintenance'] ?? null;
    if (!$wpContratId) continue;
    $wpNumero = $postmeta[$wpContratId]['contrat_id_vits'] ?? 'WP-'.$wpContratId;
    $contrat = Contrat::where('numero_contrat_vits', $wpNumero)->first();
    if (!$contrat) continue;
    $date = parseDate($meta['date'] ?? null);
    if (!$date) continue;
    $bon = 'WP-'.$pid;
    if (Intervention::where('numero_bon_kizeo', $bon)->exists()) continue;
    $forfait  = (int)($meta['forfait_temps'] ?? 0);
    $estFlash = ($meta['forfait'] ?? '1') === '0';
    Intervention::create([
        'contrat_id' => $contrat->id, 'date_intervention' => $date,
        'numero_bon_kizeo' => $bon, 'type' => $estFlash ? 'flash' : 'site',
        'duree_minutes' => $forfait, 'statut' => 'traitee',
        'type_tri' => 'standard', 'source_kizeo' => false
    ]);
    $c++;
}
echo "$c interventions importees\n";
echo "Import termine !\n";

function parseDate($v) {
    if (!$v || strlen($v) < 6) return null;
    try {
        if (strlen($v) === 8 && is_numeric($v)) return Carbon::createFromFormat('Ymd', $v)->toDateString();
        return Carbon::parse($v)->toDateString();
    } catch (Exception $e) { return null; }
}
