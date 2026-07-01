<?php
namespace App\Observers;

use App\Models\Client;
use App\Services\KizeoService;

class ClientObserver
{
    public function created(Client $client): void
    {
        app(KizeoService::class)->syncClientsVersKizeo();
    }

    public function updated(Client $client): void
    {
        app(KizeoService::class)->syncClientsVersKizeo();
    }

    public function deleted(Client $client): void
    {
        app(KizeoService::class)->syncClientsVersKizeo();
    }
}
