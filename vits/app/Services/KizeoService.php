<?php
namespace App\Services;

use App\Models\Client;
use App\Models\Contrat;
use App\Models\Intervention;
use App\Models\KizeoIgnore;
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
    const FORM_NOUVEAU  = '1185604';
    const LIST_CLIENTS  = '21312';
    const LIST_SOCIETES = '496529';

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
            Cache::put('kizeo_derniere_import', now()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'), now()->addDays(30));
            Cache::forget('kizeo_non_lus');

            $log = [
                'debut'       => $debut->format('H:i:s'),
                'fin'         => now()->setTimezone(config('app.timezone'))->format('H:i:s'),
                'statut'      => $result['errors'] === 0 ? 'ok' : 'partiel',
                'api_ok'      => true,
                'imported'    => $result['imported'],
                'skipped'     => $result['skipped'],
                'sans_contrat' => $result['sans_contrat'] ?? 0,
                'errors'      => $result['errors'],
                'message'     => $result['message'],
                'details'     => [],
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

        $imported    = 0;
        $sansContrat = 0;
        $errors      = 0;

        foreach ([self::FORM_SITE, self::FORM_DISTANCE, self::FORM_NOUVEAU] as $formId) {
            $result      = $this->importerFormulaire($formId);
            $imported    += $result['imported'];
            $sansContrat += $result['sansContrat'] ?? 0;
            $errors      += $result['errors'];
        }

        $message = "{$imported} intervention(s) importée(s)";
        if ($sansContrat > 0) $message .= ", {$sansContrat} sans contrat";
        if ($errors > 0)      $message .= ", {$errors} erreur(s)";

        $log = [
            'debut'       => $debut->format('H:i:s'),
            'fin'         => now()->format('H:i:s'),
            'statut'      => $errors === 0 ? 'ok' : 'partiel',
            'api_ok'      => true,
            'imported'    => $imported,
            'sans_contrat' => $sansContrat,
            'errors'      => $errors,
            'message'     => $message,
            'details'     => [],
        ];
        Cache::put('kizeo_import_log', $log, now()->addDays(7));
        Cache::put('kizeo_derniere_import', now()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'), now()->addDays(30));
        Cache::forget('kizeo_non_lus');

        return [
            'success'     => true,
            'imported'    => $imported,
            'sans_contrat' => $sansContrat,
            'errors'      => $errors,
            'message'     => $message,
        ];
    }

    public function importerParPeriode(string $dateDebut, string $dateFin): array
    {
        if (!$this->apiKey) {
            return ['success' => false, 'message' => 'Clé API non configurée'];
        }

        $imported    = 0;
        $skipped     = 0;
        $sansContrat = 0;
        $errors      = 0;

        foreach ([self::FORM_SITE, self::FORM_DISTANCE, self::FORM_NOUVEAU] as $formId) {
            $page = 0;
            do {
                $response = Http::timeout(60)->retry(2, 3000)->withHeaders([
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
                        if ($statut === 'imported')       $imported++;
                        elseif ($statut === 'skipped')     $skipped++;
                        elseif ($statut === 'sans_contrat') $sansContrat++;
                        else $errors++;
                    } catch (\Exception $e) {
                        Log::error("Kizeo traitement erreur: " . $e->getMessage());
                        $errors++;
                    }
                }

                $page++;
            } while (count($data) === 100 && ($page * 100) < $total);
        }

        $message = "{$imported} importée(s), {$skipped} doublon(s) ignoré(s)";
        if ($sansContrat > 0) $message .= ", {$sansContrat} sans contrat";
        if ($errors > 0)      $message .= ", {$errors} erreur(s)";
        $message .= " [{$dateDebut} → {$dateFin}]";

        return [
            'success'     => true,
            'imported'    => $imported,
            'skipped'     => $skipped,
            'sans_contrat' => $sansContrat,
            'errors'      => $errors,
            'message'     => $message,
        ];
    }

    protected function importerFormulaire(string $formId): array
    {
        $imported    = 0;
        $skipped     = 0;
        $sansContrat = 0;
        $errors      = 0;

        try {
            $response = Http::timeout(60)->retry(2, 3000)->withHeaders([
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
                    if ($statut === 'imported')       $imported++;
                    elseif ($statut === 'skipped')     $skipped++;
                    elseif ($statut === 'sans_contrat') $sansContrat++;
                    else $errors++;
                } catch (\Exception $e) {
                    Log::error("Kizeo traitement erreur: " . $e->getMessage());
                    $errors++;
                }
            }

            if (!empty($data)) {
                $ids = array_column($data, '_id');
                Http::timeout(60)->retry(2, 3000)->withHeaders([
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

        return compact('imported', 'skipped', 'sansContrat', 'errors');
    }

    protected function chargerMappings(string $formId): array
    {
        return \DB::table('kizeo_field_mappings')
            ->where('form_id', $formId)
            ->where('is_active', true)
            ->get()
            ->keyBy('kizeo_field')
            ->toArray();
    }

    protected function appliquerTransform(string $transform, $valeur, array $record): mixed
    {
        return match($transform) {
            'substr_11_5'       => isset($record['_answer_time']) ? substr($record['_answer_time'], 11, 5) : null,
            'heures_fois_60'    => (int)((float)$valeur * 60),
            'parse_duree_20'    => $this->parserDuree((string)$valeur),
            'bool_oui_non'      => in_array(strtolower(trim((string)$valeur)), ['oui', 'cochée', 'coché', '1']),
            'cloture_ancien'    => strtolower(trim((string)$valeur)) === 'clôturée' ? 'traitee' : 'non-traitee',
            'cloture_nouveau'   => in_array(strtolower(trim((string)$valeur)), ['1', 'cochée', 'coché', 'oui']) ? 'traitee' : 'non-traitee',
            'logique_flash'     => strtolower(trim((string)$valeur)) === 'oui',
            'type_nouveau'      => match(strtolower(trim((string)$valeur))) {
                'sur site'  => 'site',
                'a distance', 'à distance' => 'distance',
                'atelier'   => 'site',
                default     => 'site',
            },
            'concat_prenom_nom' => trim(($record['_first_name'] ?? '') . ' ' . ($record['_last_name'] ?? '')),
            'file_attente'      => (string)$valeur,
            'raw_data'          => null,
            default             => $valeur,
        };
    }

    protected function traiterEnregistrement(array $record, string $formId): string
    {
        $bonNumero = (string)($record['_id'] ?? '');
        if (!$bonNumero) return 'error';

        // Vérifier doublon — inclut les interventions soft-deleted
        if (Intervention::withTrashed()->where('numero_bon_kizeo', $bonNumero)->exists()) return 'skipped';

        $date = $record['date'] ?? null;
        if (!$date) return 'error';

        // Nom client selon le formulaire
        if ($formId === self::FORM_NOUVEAU) {
            $nomClient = trim($record['societe'] ?? '');
            $autreClientKey = 'autre_societe';
        } else {
            $nomClient = trim($record['client'] ?? '');
            $autreClientKey = 'autre_client';
        }

        $nomClient = trim($nomClient, '() ');

        // Client inconnu → file d'attente
        if (in_array($nomClient, ['-', '?', ''], true)) {
            $autreClient = trim($record[$autreClientKey] ?? '');
            if (!$autreClient) return 'skipped';

            [$type, $estFlash, $dureeMinutes, $statut, $heureArrivee, $technicien, $dureeDevisMinutes,
             $commentaires, $horsHeureOuvree, $piecesDetachees, $demandeAnnexe, $donneurOrdre,
             $nTicket, $nDevis, $rawData] = $this->extraireChampsTechniques($record, $formId);

            // Stocker en file d'attente
            \DB::table('intervention_pending_clients')->insert([
                'nom_saisi'         => $autreClient,
                'form_id'           => $formId,
                'numero_bon_kizeo'  => $bonNumero,
                'raw_data'          => json_encode($rawData),
                'statut'            => 'en_attente',
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // Créer quand même l'intervention en hors_contrat
            Intervention::create([
                'contrat_id'         => null,
                'client_nom'         => $autreClient,
                'date_intervention'  => $date,
                'heure_intervention' => $heureArrivee,
                'technicien'         => $technicien,
                'numero_bon_kizeo'   => $bonNumero,
                'type'               => $type,
                'duree_minutes'      => $estFlash ? 0 : $dureeMinutes,
                'duree_devis_minutes'=> $dureeDevisMinutes,
                'statut'             => $statut,
                'type_tri'           => 'hors-contrat',
                'source_kizeo'       => true,
                'source_manuelle'    => false,
                'deductible'         => false,
                'hors_contrat'       => true,
                'commentaires'       => $commentaires ?: null,
                'hors_heure_ouvree'  => $horsHeureOuvree,
                'pieces_detachees'   => $piecesDetachees ?: null,
                'demande_annexe'     => $demandeAnnexe ?: null,
                'donneur_ordre'      => $donneurOrdre ?: null,
                'n_ticket'           => $nTicket ?: null,
                'n_devis'            => $nDevis ?: null,
                'raw_data'           => $rawData,
            ]);
            return 'imported';
        }

        // Normaliser apostrophes
        $nomClient = str_replace("\u{2019}", "'", $nomClient);
        $nomClient = str_replace("\u{2018}", "'", $nomClient);

        // Aliases
        $aliases = [
            'ACTA PREVENTION' => 'VANBERG Prévention',
            'ACTA PRÉVENTION' => 'VANBERG Prévention',
            'ADV FORMATION'   => 'NOUBIZ',
            'ULTRASERVICE'    => 'FLOLISVA',
            "L'AVENTURE"      => 'AWEN Propreté',
        ];
        if (isset($aliases[$nomClient])) $nomClient = $aliases[$nomClient];

        // Trouver le client
        $client = Client::where('nom_societe', $nomClient)->first()
            ?? Client::where('nom_societe', 'like', '%'.$nomClient.'%')->first();

        if (!$client) {
            Log::info("Kizeo : nouveau client créé automatiquement '{$nomClient}' - bon {$bonNumero}");
            $client = Client::create([
                'nom_societe'    => $nomClient,
                'nom_signataire' => '',
                'statut'         => 'actif',
            ]);
        }

        // Trouver le contrat
        $contrat = Contrat::where('client_id', $client->id)->where('statut', 'en-cours')->first()
            ?? Contrat::where('client_id', $client->id)->orderBy('date_fin', 'desc')->first();

        [$type, $estFlash, $dureeMinutes, $statut, $heureArrivee, $technicien, $dureeDevisMinutes,
         $commentaires, $horsHeureOuvree, $piecesDetachees, $demandeAnnexe, $donneurOrdre,
         $nTicket, $nDevis, $rawData] = $this->extraireChampsTechniques($record, $formId);

        // Déductible
        $contratIndicateur = strtolower(trim($record['contrat'] ?? ''));
        $forfaitIndicateur = strtolower(trim($record['forfait'] ?? $record['forfait_devis'] ?? ''));
        $flashIndicateur   = strtolower(trim($record['flash'] ?? ''));
        $deductible = (bool)(
            ($flashIndicateur === 'oui')
            || ($contratIndicateur === 'oui' && $forfaitIndicateur !== 'oui')
        );

        $champCommuns = [
            'client_nom'         => $client->nom_societe,
            'date_intervention'  => $date,
            'heure_intervention' => $heureArrivee,
            'technicien'         => $technicien,
            'numero_bon_kizeo'   => $bonNumero,
            'type'               => $type,
            'duree_minutes'      => $estFlash ? 0 : $dureeMinutes,
            'duree_devis_minutes'=> $dureeDevisMinutes,
            'statut'             => $statut,
            'source_kizeo'       => true,
            'deductible'         => $deductible,
            'commentaires'       => $commentaires ?: null,
            'hors_heure_ouvree'  => $horsHeureOuvree,
            'pieces_detachees'   => $piecesDetachees ?: null,
            'demande_annexe'     => $demandeAnnexe ?: null,
            'donneur_ordre'      => $donneurOrdre ?: null,
            'n_ticket'           => $nTicket ?: null,
            'n_devis'            => $nDevis ?: null,
            'raw_data'           => $rawData,
        ];

        if (!$contrat || ($contrat->date_debut && Carbon::parse($date)->lt($contrat->date_debut))) {
            Intervention::create(array_merge($champCommuns, [
                'contrat_id'  => null,
                'type_tri'    => 'hors-contrat',
                'hors_contrat'=> true,
            ]));
            return 'imported';
        }

        Intervention::create(array_merge($champCommuns, [
            'contrat_id'  => $contrat->id,
            'type_tri'    => $deductible ? 'standard' : 'hors-contrat',
            'hors_contrat'=> false,
        ]));

        if ($estFlash) {
            Intervention::recalculerFlash($contrat->id, $date);
        }

        return 'imported';
    }

    protected function extraireChampsTechniques(array $record, string $formId): array
    {
        $mappings = $this->chargerMappings($formId);

        // raw_data = tout le payload brut
        $rawData = $record;

        // Technicien
        $technicien = null;
        if (!empty($record['_user_name']) && preg_match('/\(.*\s+(\w+)\)/', $record['_user_name'], $m)) {
            $technicien = $m[1];
        }
        if (!$technicien) {
            $technicien = trim(($record['_first_name'] ?? '') . ' ' . ($record['_last_name'] ?? '')) ?: null;
        }

        // Heure arrivée
        $heureArrivee = isset($record['_answer_time']) ? substr($record['_answer_time'], 11, 5) : null;

        // Statut
        $statut = 'non-traitee';
        if ($formId === self::FORM_NOUVEAU) {
            $statut = in_array(strtolower(trim($record['inter_cloture'] ?? '')), ['1', 'cochée', 'coché', 'oui']) ? 'traitee' : 'non-traitee';
        } else {
            $statut = strtolower($record['intervention'] ?? '') === 'clôturée' ? 'traitee' : 'non-traitee';
        }

        // Type et flash
        $estFlash = strtolower($record['flash'] ?? 'non') === 'oui';
        $type = 'site';

        if ($formId === self::FORM_NOUVEAU) {
            $typeRaw = strtolower(trim($record['type_intervention'] ?? ''));
            $type = match($typeRaw) {
                'sur site'  => 'site',
                'a distance', 'à distance' => 'distance',
                'atelier'   => 'site',
                default     => 'site',
            };
        } elseif ($formId === self::FORM_DISTANCE) {
            $type = 'distance';
        }

        if ($estFlash) $type = 'flash';

        // Durée
        $dureeMinutes = 0;
        if ($formId === self::FORM_NOUVEAU) {
            if ($type === 'site') {
                $dureeMinutes = (int)((float)($record['duree_inter_site_1'] ?? 0) * 60);
            } else {
                $dureeMinutes = $this->parserDuree($record['duree_inter_distant_2'] ?? '');
            }
        } elseif ($formId === self::FORM_DISTANCE) {
            $dureeMinutes = $this->parserDuree($record['forfait_temps'] ?? '');
            if ($dureeMinutes === 0) {
                $dureeMinutes = $this->parserDuree($record['temps'] ?? '');
            }
        } else {
            $dureeMinutes = (int)((float)($record['temps'] ?? 0) * 60);
        }

        // Durée devis
        $dureeDevisMinutes = 0;
        if ($formId === self::FORM_NOUVEAU) {
            $dureeDevisMinutes = (int)((float)($record['temps_complementaire'] ?? 0) * 60);
        } elseif ($formId === self::FORM_SITE) {
            $dureeDevisMinutes = (int)((float)($record['temps_du_devis'] ?? 0) * 60);
        }

        // Champs supplémentaires
        $commentaires    = trim($record['commentaires'] ?? '');
        $horsHeureOuvree = false;
        if ($formId === self::FORM_NOUVEAU) {
            $horsHeureOuvree = in_array(strtolower(trim($record['hors_heure_ouvree'] ?? '')), ['oui', 'cochée', 'coché', '1']);
        } elseif ($formId === self::FORM_DISTANCE) {
            $horsHeureOuvree = strtolower($record['heures_ouvrees'] ?? '') === 'oui';
        } else {
            $horsHeureOuvree = strtolower($record['intervention_en_dehors_des_he'] ?? '') === 'oui';
        }

        $piecesDetachees = trim($record['pieces_detachees'] ?? '');
        $demandeAnnexe   = trim($record['demande_annexe'] ?? $record['que_souhaite_le_client_'] ?? '');
        $donneurOrdre    = trim($record['donneur_ordre'] ?? $record['demandeur'] ?? '');
        $nTicket         = trim($record['n_ticket'] ?? $record['ticket'] ?? '');
        $nDevis          = trim($record['n_devis'] ?? $record['n_de_devis'] ?? '');

        return [
            $type,
            $estFlash,
            $dureeMinutes,
            $statut,
            $heureArrivee,
            $technicien,
            $dureeDevisMinutes,
            $commentaires,
            $horsHeureOuvree,
            $piecesDetachees,
            $demandeAnnexe,
            $donneurOrdre,
            $nTicket,
            $nDevis,
            $rawData,
        ];
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

    public function reventilerInterventionsHorsContrat(Client $client): array
    {
        $interventions = \App\Models\Intervention::where('client_nom', $client->nom_societe)
            ->where('hors_contrat', true)
            ->get();

        $reventilees = 0;
        $restantes   = 0;

        foreach ($interventions as $intervention) {
            $date   = $intervention->date_intervention instanceof \Carbon\Carbon
                ? $intervention->date_intervention
                : \Carbon\Carbon::parse($intervention->date_intervention);

            $contrat = \App\Models\Contrat::where('client_id', $client->id)
                ->where('date_debut', '<=', $date->toDateString())
                ->where('date_fin',   '>=', $date->toDateString())
                ->first();

            if ($contrat) {
                $intervention->update([
                    'contrat_id'  => $contrat->id,
                    'hors_contrat' => false,
                    'type_tri'    => 'standard',
                ]);
                $reventilees++;
            } else {
                $restantes++;
            }
        }

        return ['reventilees' => $reventilees, 'restantes' => $restantes];
    }

    public function syncClientsDepuisListe(): array
    {
        if (!$this->apiKey) {
            return ['success' => false, 'message' => 'Clé API non configurée', 'created' => 0, 'skipped' => 0];
        }

        $response = Http::timeout(30)->withHeaders(['Authorization' => $this->apiKey])
            ->get("{$this->baseUrl}/lists/" . self::LIST_CLIENTS);

        if (!$response->successful()) {
            return ['success' => false, 'message' => 'Erreur API Kizeo (' . $response->status() . ')', 'created' => 0, 'skipped' => 0];
        }

        $items   = $response->json('list.items', []);
        $created = 0;
        $skipped = 0;

        foreach ($items as $item) {
            $cols       = explode('|', $item);
            $nom        = strstr($cols[0] ?? '', ':', true) ?: ($cols[0] ?? '');
            $email      = strstr($cols[1] ?? '', ':', true) ?: ($cols[1] ?? '');
            $signataire = strstr($cols[2] ?? '', ':', true) ?: ($cols[2] ?? '');
            $code       = strstr($cols[3] ?? '', ':', true) ?: ($cols[3] ?? '');
            $actif      = (int)(strstr($cols[4] ?? '0', ':', true) ?: ($cols[4] ?? '0'));

            $nom = trim($nom);
            if (!$nom || $nom === '-') {
                continue;
            }

            if (Client::where('nom_societe', $nom)->exists()) {
                $skipped++;
                continue;
            }

            Client::create([
                'nom_societe'           => $nom,
                'email_signataire'      => $email ?: null,
                'nom_signataire'        => $signataire ?: '',
                'numero_client_kizeo'   => $code ?: null,
                'statut'                => $actif ? 'actif' : 'inactif',
            ]);
            $created++;
        }

        return [
            'success' => true,
            'message' => "{$created} client(s) créé(s), {$skipped} déjà existant(s)",
            'created' => $created,
            'skipped' => $skipped,
        ];
    }

    public function getNombreNonLus(): array
    {
        $result = ['site' => 0, 'distance' => 0];

        foreach ([self::FORM_SITE => 'site', self::FORM_DISTANCE => 'distance'] as $formId => $type) {
            try {
                $response = Http::timeout(60)->retry(2, 3000)->withHeaders([
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

    public function syncClientsVersKizeo(): array
    {
        if (!$this->apiKey) {
            return ['success' => false, 'message' => 'Clé API non configurée', 'synced' => 0];
        }

        $clients = Client::whereIn('statut', ['actif', 'sans_contrat'])
            ->orderBy('nom_societe')
            ->get();

        $items = [' Autre client||||0|'];

        foreach ($clients as $client) {
            $nom        = str_replace(['|', "\n", "\r"], ' ', $client->nom_societe ?? '');
            $email      = str_replace(['|', "\n", "\r"], ' ', $client->email_signataire ?? '');
            $signataire = str_replace(['|', "\n", "\r"], ' ', $client->nom_signataire ?? '');
            $code       = str_replace(['|', "\n", "\r"], ' ', $client->numero_client_kizeo ?? '');
            $contrat    = $client->statut === 'actif' ? '1' : '0';
            $items[]    = "{$nom}|{$email}|{$signataire}|{$code}|{$contrat}|";
        }

        $response = Http::timeout(30)->withHeaders([
            'Authorization' => $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->put("{$this->baseUrl}/lists/" . self::LIST_SOCIETES, [
            'items' => $items,
        ]);

        if (!$response->successful()) {
            Log::error('Kizeo syncClientsVersKizeo erreur : ' . $response->status() . ' ' . $response->body());
            return ['success' => false, 'message' => 'Erreur API Kizeo (' . $response->status() . ')', 'synced' => 0];
        }

        Log::info('Kizeo syncClientsVersKizeo : ' . count($items) . ' client(s) synchronisé(s)');

        return [
            'success' => true,
            'message' => count($items) . ' client(s) synchronisé(s) vers Kizeo',
            'synced'  => count($items),
        ];
    }
}