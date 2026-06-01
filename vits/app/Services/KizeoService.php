<?php
namespace App\Services;

use App\Models\Client;
use App\Models\Contrat;
use App\Models\Intervention;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class KizeoService
{
    protected $apiKey;
    protected $baseUrl = 'https://www.kizeoforms.com/rest/v3';

    const FORM_SITE     = '45252';
    const FORM_DISTANCE = '108738';

    public function __construct()
    {
        $this->apiKey = config('vits.kizeo_api_key');
    }

    public function importUnread(): array
    {
        return $this->importDepuisDerniereIntervention();
    }

    public function importDepuisDerniereIntervention(): array
    {
        $debut = now();

        if (!$this->apiKey) {
            $log = ['debut' => $debut->format('H:i:s'), 'fin' => now()->format('H:i:s'),
                    'statut' => 'erreur', 'api_ok' => false, 'imported' => 0,
                    'skipped' => 0, 'errors' => 1, 'message' => 'Clé API non configurée', 'details' => []];
            Cache::put('kizeo_import_log', $log, now()->addDays(7));
            return ['success' => false, 'message' => 'Clé API non configurée'];
        }

        // Date de départ : MAX(date_intervention), sinon MAX(created_at), sinon -30 jours
        $derniereDate = Intervention::max('date_intervention')
            ?? Intervention::max('created_at');
        $dateDebut = $derniereDate
            ? Carbon::parse($derniereDate)->format('Y-m-d')
            : Carbon::now()->subDays(30)->format('Y-m-d');
        $dateFin = Carbon::now()->format('Y-m-d');

        $result = $this->importerParPeriode($dateDebut, $dateFin);

        if ($result['success']) {
            Cache::put('kizeo_derniere_import', now()->format('Y-m-d H:i:s'), now()->addDays(30));
            Cache::forget('kizeo_non_lus');

            $log = [
                'debut'    => $debut->format('H:i:s'),
                'fin'      => now()->format('H:i:s'),
                'statut'   => $result['errors'] === 0 ? 'ok' : 'partiel',
                'api_ok'   => true,
                'imported' => $result['imported'],
                'skipped'  => $result['skipped'],
                'errors'   => $result['errors'],
                'message'  => $result['message'],
                'details'  => [],
            ];
            Cache::put('kizeo_import_log', $log, now()->addDays(7));
        }

        return $result;
    }

    public function importerInterventions(): array
    {
        $debut = now();

        if (!$this->apiKey) {
            $log = [
                'debut'    => $debut->format('H:i:s'),
                'fin'      => now()->format('H:i:s'),
                'statut'   => 'erreur',
                'api_ok'   => false,
                'imported' => 0,
                'errors'   => 1,
                'message'  => 'Clé API non configurée',
                'details'  => [],
            ];
            Cache::put('kizeo_import_log', $log, now()->addDays(7));
            Log::warning('Kizeo : clé API non configurée');
            return ['success' => false, 'message' => 'Clé API non configurée'];
        }

        $imported = 0;
        $errors   = 0;

        foreach ([self::FORM_SITE, self::FORM_DISTANCE] as $formId) {
            $result    = $this->importerFormulaire($formId);
            $imported += $result['imported'];
            $errors   += $result['errors'];
        }

        $log = [
            'debut'    => $debut->format('H:i:s'),
            'fin'      => now()->format('H:i:s'),
            'statut'   => $errors === 0 ? 'ok' : 'partiel',
            'api_ok'   => true,
            'imported' => $imported,
            'errors'   => $errors,
            'message'  => "{$imported} intervention(s) importée(s)" . ($errors > 0 ? ", {$errors} erreur(s)" : ''),
            'details'  => [],
        ];
        Cache::put('kizeo_import_log', $log, now()->addDays(7));
        Cache::put('kizeo_derniere_import', now()->format('Y-m-d H:i:s'), now()->addDays(30));
        Cache::forget('kizeo_non_lus');

        return [
            'success'  => true,
            'imported' => $imported,
            'errors'   => $errors,
            'message'  => $log['message'],
        ];
    }

    public function importerParPeriode(string $dateDebut, string $dateFin): array
    {
        if (!$this->apiKey) {
            return ['success' => false, 'message' => 'Clé API non configurée'];
        }

        $imported = 0;
        $skipped  = 0;
        $errors   = 0;

        foreach ([self::FORM_SITE, self::FORM_DISTANCE] as $formId) {
            $page = 0;
            do {
                $response = Http::withHeaders([
                    'Authorization' => $this->apiKey,
                    'Content-Type'  => 'application/json',
                ])->post("{$this->baseUrl}/forms/{$formId}/data/advanced", [
                    'filters' => [
                        ['field' => 'date', 'operator' => '>=', 'val' => $dateDebut],
                        ['field' => 'date', 'operator' => '<=', 'val' => $dateFin],
                    ],
                    'order'          => [['col' => 'date', 'order' => 'asc']],
                    'page'           => $page,
                    'recordsPerPage' => 100,
                ]);

                if (!$response->successful()) {
                    $errors++;
                    break;
                }

                $data  = $response->json('data', []);
                $total = $response->json('recordsFiltered', 0);

                foreach ($data as $record) {
                    try {
                        $statut = $this->traiterEnregistrement($record, $formId);
                        if ($statut === 'imported') $imported++;
                        elseif ($statut === 'skipped') $skipped++;
                        else $errors++;
                    } catch (\Exception $e) {
                        Log::error("Kizeo traitement erreur: " . $e->getMessage());
                        $errors++;
                    }
                }

                $page++;
            } while (count($data) === 100 && ($page * 100) < $total);
        }

        return [
            'success'  => true,
            'imported' => $imported,
            'skipped'  => $skipped,
            'errors'   => $errors,
            'message'  => "{$imported} importée(s), {$skipped} doublon(s) ignoré(s)" . ($errors > 0 ? ", {$errors} erreur(s)" : '') . " [{$dateDebut} → {$dateFin}]",
        ];
    }

    protected function importerFormulaire(string $formId): array
    {
        $imported = 0;
        $skipped  = 0;
        $errors   = 0;

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type'  => 'application/json',
            ])->get("{$this->baseUrl}/forms/{$formId}/data/unread/vitsmiki/100");

            if (!$response->successful()) {
                return ['imported' => 0, 'skipped' => 0, 'errors' => 1];
            }

            $data = $response->json('data', []);

            foreach ($data as $record) {
                try {
                    $statut = $this->traiterEnregistrement($record, $formId);
                    if ($statut === 'imported') $imported++;
                    elseif ($statut === 'skipped') $skipped++;
                    else $errors++;
                } catch (\Exception $e) {
                    Log::error("Kizeo traitement erreur: " . $e->getMessage());
                    $errors++;
                }
            }

            if (!empty($data)) {
                $ids = array_column($data, '_id');
                Http::withHeaders([
                    'Authorization' => $this->apiKey,
                    'Content-Type'  => 'application/json',
                ])->post("{$this->baseUrl}/forms/{$formId}/markasreadbyaction/vitsmiki", [
                    'data_ids' => $ids,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Kizeo formulaire {$formId} erreur: " . $e->getMessage());
            $errors++;
        }

        return compact('imported', 'skipped', 'errors');
    }

    protected function traiterEnregistrement(array $record, string $formId): string
    {
        $bonNumero = (string)($record['_id'] ?? '');
        if (!$bonNumero) return 'error';

        // Doublon : intervention déjà en base avec ce numéro de bon
        if (Intervention::where('numero_bon_kizeo', $bonNumero)->exists()) return 'skipped';

        $nomClient = trim($record['client'] ?? '');
        $date      = $record['date'] ?? null;

        if (!$nomClient || !$date) return 'error';

        // Nettoyer le nom client (parenthèses ex: "(COEXPAU)")
        $nomClient = trim($nomClient, '() ');

        // Normaliser les apostrophes
        $nomClient = str_replace("\u{2019}", "'", $nomClient);
        $nomClient = str_replace("\u{2018}", "'", $nomClient);

        // Aliases : anciens noms -> noms actuels
        $aliases = [
            'ACTA PREVENTION' => 'VANBERG Prévention',
            'ACTA PRÉVENTION' => 'VANBERG Prévention',
            'ADV FORMATION'   => 'NOUBIZ',
            'ULTRASERVICE'    => 'FLOLISVA',
            "L'AVENTURE"      => 'AWEN Propreté',
        ];
        if (isset($aliases[$nomClient])) {
            $nomClient = $aliases[$nomClient];
        }

        // Trouver le client
        $client = Client::where('nom_societe', $nomClient)->first()
            ?? Client::where('nom_societe', 'like', '%'.$nomClient.'%')->first();

        if (!$client) {
            Log::info("Kizeo : client non trouvé '{$nomClient}' - bon {$bonNumero}");
            return 'error';
        }

        // Trouver le contrat actif à la date de l'intervention
        $contrat = Contrat::where('client_id', $client->id)
            ->where('statut', 'en-cours')
            ->first();

        if (!$contrat) {
            $contrat = Contrat::where('client_id', $client->id)
                ->orderBy('date_fin', 'desc')
                ->first();
        }

        if (!$contrat) {
            Log::info("Kizeo : pas de contrat pour '{$nomClient}' - bon {$bonNumero}");
            return 'error';
        }

        // Déductible
        $contratIndicateur = strtolower(trim($record['contrat'] ?? ''));
        $forfaitIndicateur = strtolower(trim($record['forfait'] ?? ''));
        $flashIndicateur   = strtolower(trim($record['flash']   ?? ''));
        $deductible = (bool)(
            ($flashIndicateur === 'oui')
            || ($contratIndicateur === 'oui' && $forfaitIndicateur !== 'oui')
        );

        // Type
        $estFlash = false;
        $type     = 'site';

        if ($formId === self::FORM_DISTANCE) {
            $type     = 'distance';
            $estFlash = strtolower($record['flash'] ?? 'non') === 'oui';
        }

        if ($estFlash) $type = 'flash';

        // Durée
        $dureeMinutes = 0;
        if ($formId === self::FORM_DISTANCE) {
            $dureeMinutes = $this->parserDuree($record['forfait_temps'] ?? '');
            if ($dureeMinutes === 0) {
                $dureeMinutes = $this->parserDuree($record['temps'] ?? '');
            }
        } else {
            $heures = (float)($record['temps'] ?? 0);
            $dureeMinutes = (int)($heures * 60);
        }

        $statut = strtolower($record['intervention'] ?? '') === 'clôturée' ? 'traitee' : 'non-traitee';

        Intervention::create([
            'contrat_id'        => $contrat->id,
            'date_intervention' => $date,
            'numero_bon_kizeo'  => $bonNumero,
            'type'              => $type,
            'duree_minutes'     => $estFlash ? 0 : $dureeMinutes,
            'statut'            => $statut,
            'type_tri'          => $deductible ? 'standard' : 'hors-contrat',
            'source_kizeo'      => true,
            'deductible'        => $deductible,
        ]);

        if ($estFlash) {
            Intervention::recalculerFlash($contrat->id, $date);
        }

        return 'imported';
    }

    public function parserDuree(string $duree): int
    {
        $duree = trim(strtolower($duree));
        if (!$duree) return 0;

        $minutes = 0;

        if (preg_match('/(\d+)h(\d*)/i', $duree, $m)) {
            $minutes += (int)$m[1] * 60;
            if (!empty($m[2])) $minutes += (int)$m[2];
            return $minutes;
        }

        if (preg_match('/(\d+)\s*min/i', $duree, $m)) {
            return (int)$m[1];
        }

        if (is_numeric($duree)) {
            return (int)$duree;
        }

        return 0;
    }

    public function getNombreNonLus(): array
    {
        $result = ['site' => 0, 'distance' => 0];

        foreach ([self::FORM_SITE => 'site', self::FORM_DISTANCE => 'distance'] as $formId => $type) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => $this->apiKey,
                ])->get("{$this->baseUrl}/forms/{$formId}/data/unread/vitsmiki/1");

                if ($response->successful()) {
                    $result[$type] = $response->json('recordsTotal', 0);
                }
            } catch (\Exception $e) {
                Log::error("Kizeo getNonLus erreur: " . $e->getMessage());
            }
        }

        return $result;
    }
}