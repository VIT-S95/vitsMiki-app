<?php
namespace App\Services;

use App\Models\Client;
use App\Models\Contrat;
use App\Models\Intervention;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class KizeoService
{
    protected $apiKey;
    protected $baseUrl = 'https://www.kizeoforms.com/rest/v3';

    // IDs des formulaires Kizeo
    const FORM_SITE     = '45252';
    const FORM_DISTANCE = '108738';

    public function __construct()
    {
        $this->apiKey = config('vits.kizeo_api_key');
    }

    /**
     * Import automatique (cron) — récupère les nouvelles interventions depuis la dernière synchro
     */
    public function importerInterventions(): array
    {
        if (!$this->apiKey) {
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

        return [
            'success'  => true,
            'imported' => $imported,
            'errors'   => $errors,
            'message'  => "{$imported} intervention(s) importée(s)",
        ];
    }

    /**
     * Import en masse sur une plage de dates (utilisé pour l'import initial)
     */
    public function importerParPeriode(string $dateDebut, string $dateFin): array
    {
        if (!$this->apiKey) {
            return ['success' => false, 'message' => 'Clé API non configurée'];
        }

        $imported = 0;
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
                        if ($this->traiterEnregistrement($record, $formId)) $imported++;
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
            'errors'   => $errors,
            'message'  => "{$imported} intervention(s) importée(s) entre {$dateDebut} et {$dateFin}",
        ];
    }

    /**
     * Import d'un formulaire — récupère les données non lues
     */
    protected function importerFormulaire(string $formId): array
    {
        $imported = 0;
        $errors   = 0;

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type'  => 'application/json',
            ])->get("{$this->baseUrl}/forms/{$formId}/data/unread/vitsmiki/100");

            if (!$response->successful()) {
                return ['imported' => 0, 'errors' => 1];
            }

            $data = $response->json('data', []);

            foreach ($data as $record) {
                try {
                    if ($this->traiterEnregistrement($record, $formId)) {
                        $imported++;
                    } else {
                        $errors++;
                    }
                } catch (\Exception $e) {
                    Log::error("Kizeo traitement erreur: " . $e->getMessage());
                    $errors++;
                }
            }

            // Marquer comme lues
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

        return compact('imported', 'errors');
    }

    /**
     * Traite un enregistrement Kizeo et l'insère en base
     */
    protected function traiterEnregistrement(array $record, string $formId): bool
    {
        $bonNumero = (string)($record['_id'] ?? '');
        if (!$bonNumero) return false;

        // Déjà importé ?
        if (Intervention::where('numero_bon_kizeo', $bonNumero)->exists()) return true;

        $nomClient = trim($record['client'] ?? '');
        $date      = $record['date'] ?? null;

        if (!$nomClient || !$date) return false;

        // Nettoyer le nom client (parfois entre parenthèses ex: "(COEXPAU)")
        $nomClient = trim($nomClient, '() ');

        // Trouver le client
        $client = Client::where('nom_societe', $nomClient)->first()
            ?? Client::where('nom_societe', 'like', '%'.$nomClient.'%')->first();

        if (!$client) {
            Log::info("Kizeo : client non trouvé '{$nomClient}' - bon {$bonNumero}");
            return false;
        }

        // Trouver le contrat en cours
        $contrat = Contrat::where('client_id', $client->id)
            ->where('statut', 'en-cours')
            ->first();

        if (!$contrat) {
            // Prendre le dernier contrat si pas en cours
            $contrat = Contrat::where('client_id', $client->id)
                ->orderBy('date_fin', 'desc')
                ->first();
        }

        if (!$contrat) {
            Log::info("Kizeo : pas de contrat pour '{$nomClient}' - bon {$bonNumero}");
            return false;
        }

        // Indicateur déductible
        $contratIndicateur = strtolower(trim($record['contrat'] ?? ''));
        $forfaitIndicateur = strtolower(trim($record['forfait'] ?? ''));
        $flashIndicateur   = strtolower(trim($record['flash']   ?? ''));
        // forfait=Oui = hors contrat (devis/forfait facturé), NON déductible
        // contrat=Oui ET forfait!=Oui = déductible
        // flash=Oui = déductible
        $deductible = (bool)(($flashIndicateur === 'oui')
            || ($contratIndicateur === 'oui' && $forfaitIndicateur !== 'oui'));

        // Type d'intervention
        $estFlash    = false;
        $type        = 'site';

        if ($formId === self::FORM_DISTANCE) {
            $type     = 'distance';
            $estFlash = strtolower($record['flash'] ?? 'non') === 'oui';
        }

        if ($estFlash) $type = 'flash';

        // Durée en minutes
        $dureeMinutes = 0;
        if ($formId === self::FORM_DISTANCE) {
            // Format "40min" ou "1h20" ou "2h"
            $dureeMinutes = $this->parserDuree($record['forfait_temps'] ?? '');
            if ($dureeMinutes === 0) {
                $dureeMinutes = $this->parserDuree($record['temps'] ?? '');
            }
        } else {
            // Formulaire site : temps en heures entières ex: "3"
            $heures = (float)($record['temps'] ?? 0);
            $dureeMinutes = (int)($heures * 60);
        }

        // Statut
        $statut = strtolower($record['intervention'] ?? '') === 'clôturée' ? 'traitee' : 'non-traitee';

        Intervention::create([
            'contrat_id'        => $contrat->id,
            'date_intervention' => $date,
            'numero_bon_kizeo'  => $bonNumero,
            'type'              => $type,
            'duree_minutes'     => $estFlash ? 0 : $dureeMinutes, // flash 1/3 et 2/3 = 0
            'statut'            => $statut,
            'type_tri'          => $deductible ? 'standard' : 'hors-contrat',
            'source_kizeo'      => true,
            'deductible'        => $deductible,
        ]);

        // Recalculer les flash si nécessaire
        if ($estFlash) {
            Intervention::recalculerFlash($contrat->id, $date);
        }

        return true;
    }

    /**
     * Parse une durée texte Kizeo en minutes
     * Exemples: "40min", "1h20", "2h", "1h20min", "90"
     */
    public function parserDuree(string $duree): int
    {
        $duree = trim(strtolower($duree));
        if (!$duree) return 0;

        $minutes = 0;

        // Format "1h20" ou "1h20min" ou "2h"
        if (preg_match('/(\d+)h(\d*)/i', $duree, $m)) {
            $minutes += (int)$m[1] * 60;
            if (!empty($m[2])) $minutes += (int)$m[2];
            return $minutes;
        }

        // Format "40min"
        if (preg_match('/(\d+)\s*min/i', $duree, $m)) {
            return (int)$m[1];
        }

        // Format numérique pur (en minutes)
        if (is_numeric($duree)) {
            return (int)$duree;
        }

        return 0;
    }

    /**
     * Récupère le nombre total d'interventions non lues
     */
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