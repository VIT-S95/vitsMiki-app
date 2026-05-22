<?php
namespace App\Services;
use App\Models\Client;
use App\Models\Contrat;
use App\Models\Intervention;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class KizeoService
{
    protected $apiKey;
    protected $baseUrl = 'https://www.kizeoforms.com/rest/v3';

    public function __construct()
    {
        $this->apiKey = config('vits.kizeo_api_key');
    }

    public function importerInterventions()
    {
        if (!$this->apiKey) {
            Log::warning('Kizeo : cle API non configuree');
            return ['success' => false, 'message' => 'Cle API non configuree'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type'  => 'application/json',
            ])->get("{$this->baseUrl}/forms");

            if (!$response->successful()) {
                return ['success' => false, 'message' => 'Erreur API Kizeo'];
            }

            $forms = $response->json('forms', []);
            $imported = 0;
            $errors = 0;

            foreach ($forms as $form) {
                $result = $this->importerFormulaire($form['id']);
                $imported += $result['imported'];
                $errors   += $result['errors'];
            }

            return ['success' => true, 'imported' => $imported, 'errors' => $errors, 'message' => "{$imported} intervention(s) importee(s)"];

        } catch (\Exception $e) {
            Log::error('Kizeo exception : ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function importerFormulaire($formId)
    {
        $imported = 0;
        $errors = 0;
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type'  => 'application/json',
            ])->post("{$this->baseUrl}/forms/{$formId}/data/exports/json", ['data_ids' => 'all']);

            if (!$response->successful()) return ['imported' => 0, 'errors' => 1];

            foreach ($response->json('data', []) as $record) {
                if ($this->traiterEnregistrement($record)) $imported++;
                else $errors++;
            }
        } catch (\Exception $e) {
            $errors++;
        }
        return compact('imported', 'errors');
    }

    protected function traiterEnregistrement($record)
    {
        $fields = $record['fields'] ?? [];
        $nomClient = $fields['client'] ?? $fields['nom_client'] ?? $fields['societe'] ?? null;
        $tempsMinutes = (int)($fields['temps'] ?? $fields['duree'] ?? 0);
        $date = $fields['date'] ?? $record['answer_time'] ?? now()->toDateString();
        $bonNumero = $record['id'] ?? null;
        $kizeoClientId = $record['user_id'] ?? null;

        if (!$nomClient) return false;
        if ($bonNumero && Intervention::where('numero_bon_kizeo', $bonNumero)->exists()) return true;

        $client = null;
        if ($kizeoClientId) $client = Client::where('numero_client_kizeo', $kizeoClientId)->first();
        if (!$client) $client = Client::where('nom_societe', 'like', '%'.$nomClient.'%')->first();
        if (!$client) {
            $client = Client::create([
                'nom_societe' => $nomClient,
                'nom_signataire' => 'Import Kizeo',
                'numero_client_kizeo' => $kizeoClientId,
                'statut' => 'actif',
            ]);
        }

        $contrat = Contrat::where('client_id', $client->id)->where('statut', 'en-cours')->first();
        $contratId = $contrat?->id ?? Contrat::where('client_id', $client->id)->first()?->id;

        Intervention::create([
            'contrat_id' => $contratId,
            'date_intervention' => $date,
            'numero_bon_kizeo' => $bonNumero,
            'type' => 'site',
            'duree_minutes' => $tempsMinutes,
            'statut' => $contrat ? 'traitee' : 'non-traitee',
            'type_tri' => $contrat ? 'standard' : 'hors-contrat',
            'source_kizeo' => true,
        ]);

        return true;
    }
}
