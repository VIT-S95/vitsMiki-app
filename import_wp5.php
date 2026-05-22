<?php
require __DIR__.'/vits/vendor/autoload.php';
$app = require_once __DIR__.'/vits/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Client;
use App\Models\Contrat;
use App\Models\Intervention;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

echo "Lecture ligne par ligne...\n";

$file = fopen('/workspaces/vitsMiki-app/sraza_gestion.sql', 'r');
$usermeta        = [];
$users           = [];
$postmeta        = [];
$posttitles      = [];
$contractIds     = [];
$interventionIds = [];
$clientIds       = [];
$n = 0;

while (!feof($file)) {
    $line = fgets($file, 16384);
    $n++;
    if ($n % 100000 === 0) echo "Ligne $n...\n";

    // USERS
    if (str_contains($line, 'INSERT INTO `vitswp_users`')) {
        preg_match_all("/\((\d+),\t'([^']*)',\t'[^']*',\t'[^']*',\t'([^']*)'/", $line, $m);
        foreach ($m[1] as $i => $id) {
            $users[$id] = ['login' => $m[2][$i], 'email' => $m[3][$i]];
        }
    }

    // USERMETA - capabilities
    if (str_contains($line, 'vitswp_capabilities') && str_contains($line, 'client')) {
        preg_match_all("/\(\d+,\t(\d+),\t'vitswp_capabilities',\t'([^']*)'\)/", $line, $um);
        foreach ($um[1] as $i => $uid) {
            if (str_contains($um[2][$i], 'client')) $clientIds[] = $uid;
        }
    }

    // USERMETA - vits_company
    if (str_contains($line, "'vits_company'")) {
        preg_match_all("/\(\d+,\t(\d+),\t'vits_company',\t'([^']*)'\)/", $line, $um);
        foreach ($um[1] as $i => $uid) {
            if (!isset($usermeta[$uid])) $usermeta[$uid] = [];
            $usermeta[$uid]['vits_company'] = $um[2][$i];
        }
    }

    // USERMETA - vits_id
    if (str_contains($line, "'vits_id'")) {
        preg_match_all("/\(\d+,\t(\d+),\t'vits_id',\t'([^']*)'\)/", $line, $um);
        foreach ($um[1] as $i => $uid) {
            if (!isset($usermeta[$uid])) $usermeta[$uid] = [];
            $usermeta[$uid]['vits_id'] = $um[2][$i];
        }
    }

    // USERMETA - last_name
    if (str_contains($line, "'last_name'")) {
        preg_match_all("/\(\d+,\t(\d+),\t'last_name',\t'([^']*)'\)/", $line, $um);
        foreach ($um[1] as $i => $uid) {
            if (!isset($usermeta[$uid])) $usermeta[$uid] = [];
            $usermeta[$uid]['last_name'] = $um[2][$i];
        }
    }

    // USERMETA - nickname
    if (str_contains($line, "'nickname'")) {
        preg_match_all("/\(\d+,\t(\d+),\t'nickname',\t'([^']*)'\)/", $line, $um);
        foreach ($um[1] as $i => $uid) {
            if (!isset($usermeta[$uid])) $usermeta[$uid] = [];
            $usermeta[$uid]['nickname'] = $um[2][$i];
        }
    }

    // POSTS - titre + type
    if (str_contains($line, "'contract'") || str_contains($line, "'intervention'")) {
        preg_match_all("/\((\d+),\t\d+,\t'[^']*',\t'[^']*',\t'[^']*',\t'([^']*)',\t'[^']*',\t'publish'[^)]*'(contract|intervention)'/", $line, $pm);
        foreach ($pm[1] as $i => $pid) {
            $posttitles[$pid] = $pm[2][$i];
            if ($pm[3][$i] === 'contract') $contractIds[] = $pid;
            else $interventionIds[] = $pid;
        }
    }

    // POSTMETA - on ajoute 'contrat' pour l'indicateur déductible
    $relevantKeys = ["'client'","'date_de_debut'","'date_de_fin'","'duree_contrat'","'nombre_heure_allouees'","'periodes'","'contrat_id_vits'","'contrat_de_maintenance'","'date'","'forfait_temps'","'forfait'","'contrat'"];
    foreach ($relevantKeys as $rk) {
        if (str_contains($line, $rk)) {
            preg_match_all("/\(\d+,\t(\d+),\t'([^']+)',\t'([^']*)'\)/u", $line, $pmu);
            foreach ($pmu[1] as $i => $pid) {
                $key = $pmu[2][$i]; $val = $pmu[3][$i];
                if (!str_starts_with($key, '_')) {
                    if (!isset($postmeta[$pid])) $postmeta[$pid] = [];
                    $postmeta[$pid][$key] = $val;
                }
            }
            break;
        }
    }
}
fclose($file);

$clientIds       = array_unique($clientIds);
$contractIds     = array_unique($contractIds);
$interventionIds = array_unique($interventionIds);

echo count($clientIds)." clients, ".count($contractIds)." contrats, ".count($interventionIds)." interventions\n";

// Nettoyage
echo "Nettoyage...\n";
DB::statement('PRAGMA foreign_keys = OFF');
DB::table('interventions')->truncate();
DB::table('contrats')->truncate();
DB::table('clients')->truncate();
DB::statement('PRAGMA foreign_keys = ON');
echo "Base videe\n";

// Import clients
echo "Import clients...\n";
$c = 0;
$dejaVus = [];
foreach ($clientIds as $uid) {
    $meta    = $usermeta[$uid] ?? [];
    $user    = $users[$uid] ?? [];
    $numero  = $meta['vits_id'] ?? '';
    if (empty(trim($numero))) continue;
    if (isset($dejaVus[$numero])) continue;
    $dejaVus[$numero] = true;
    $societe = $meta['vits_company'] ?? '';
    if (empty(trim($societe))) $societe = 'Client '.$uid;
    Client::create([
        'nom_societe'         => $societe,
        'nom_signataire'      => $meta['last_name'] ?? '',
        'email_signataire'    => $meta['nickname'] ?? $user['email'] ?? '',
        'numero_client_kizeo' => $numero,
        'statut'              => 'actif',
    ]);
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
    $vitsId = $usermeta[$clientWpId]['vits_id'] ?? null;
    if (!$vitsId) continue;
    $client = Client::where('numero_client_kizeo', $vitsId)->first();
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
    Contrat::create([
        'client_id'             => $client->id,
        'titre'                 => 'Contrat maintenance',
        'numero_contrat_vits'   => $numero,
        'date_debut'            => $dateDebut,
        'date_fin'              => $dateFin,
        'duree_mois'            => in_array($duree,[12,24,36])?$duree:12,
        'duree_periode_mois'    => in_array($periodes,[1,3,6,12])?$periodes:12,
        'heures_par_periode'    => $heures?:10,
        'statut'                => $statut,
        'numero_renouvellement' => 0,
    ]);
    $c++;
}
echo "$c contrats importes\n";

// Import interventions
echo "Import interventions...\n";
$c = 0;
$cSkip = 0;
foreach ($interventionIds as $pid) {
    $meta = $postmeta[$pid] ?? [];
    if (empty($meta)) continue;
    $wpContratId = $meta['contrat_de_maintenance'] ?? null;
    if (!$wpContratId) continue;
    $wpNumero = $postmeta[$wpContratId]['contrat_id_vits'] ?? null;
    if (!$wpNumero) continue;
    $contrat = Contrat::where('numero_contrat_vits', $wpNumero)->first();
    if (!$contrat) continue;
    $date = parseDate($meta['date'] ?? null);
    if (!$date) continue;

    // Indicateur déductible : champ 'contrat' = '1' ou 'oui'
    $contratIndicateur = $meta['contrat'] ?? '1';
    $deductible = in_array(strtolower($contratIndicateur), ['1', 'oui', 'yes', 'true']);

    // Numero Kizeo depuis le titre
    $titre     = $posttitles[$pid] ?? '';
    $bonNumero = null;
    if (preg_match('/n[°o]?(\d{6,9})/iu', $titre, $bm)) {
        $bonNumero = $bm[1];
    }
    if (!$bonNumero) $bonNumero = 'WP-'.$pid;

    if (Intervention::where('numero_bon_kizeo', $bonNumero)->exists()) continue;

    $forfait  = (int)($meta['forfait_temps'] ?? 0);
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
        'deductible'        => $deductible,
    ]);
    if ($deductible) $c++; else $cSkip++;
}
echo "$c interventions deductibles, $cSkip non-deductibles importees\n";
echo "Import termine !\n";

function parseDate($v) {
    if (!$v || strlen($v) < 6) return null;
    try {
        if (strlen($v) === 8 && is_numeric($v)) return Carbon::createFromFormat('Ymd', $v)->toDateString();
        return Carbon::parse($v)->toDateString();
    } catch (Exception $e) { return null; }
}