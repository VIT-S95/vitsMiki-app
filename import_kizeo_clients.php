<?php
require __DIR__.'/vits/vendor/autoload.php';
$app = require_once __DIR__.'/vits/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Client;
use Illuminate\Support\Facades\Http;

$apiKey = env('KIZEO_API_KEY');

echo "Récupération de la liste clients Kizeo...\n";

$response = Http::withHeaders(['Authorization' => $apiKey])
    ->get('https://www.kizeoforms.com/rest/v3/lists/21312');

$items = $response->json('list.items', []);

echo count($items) . " clients trouvés\n";

$c = 0;
$skip = 0;
foreach ($items as $item) {
    if (empty($item) || $item === '-:-|:') continue;

    $parts = explode('|', $item);
    if (count($parts) < 4) continue;

    $nom    = explode(':', $parts[0])[0];
    $email  = explode(':', $parts[1])[0];
    $sign   = explode(':', $parts[2])[0];
    $numero = explode(':', $parts[3])[0];
    $actif  = explode(':', $parts[4] ?? '1')[0];

    if (empty($nom) || empty($numero)) { $skip++; continue; }

    Client::updateOrCreate(
        ['numero_client_kizeo' => $numero],
        [
            'nom_societe'      => $nom,
            'email_signataire' => $email,
            'nom_signataire'   => $sign,
            'statut'           => $actif === '1' ? 'actif' : 'inactif',
        ]
    );
    $c++;
}

echo "$c clients importés, $skip ignorés\n";
echo "Import terminé !\n";
