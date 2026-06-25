<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Clients avec contrat en-cours → actif
        DB::statement("
            UPDATE clients SET statut = 'actif'
            WHERE id IN (
                SELECT DISTINCT client_id FROM contrats WHERE statut = 'en-cours'
            )
        ");

        // Clients sans contrat → sans_contrat
        DB::statement("
            UPDATE clients SET statut = 'sans_contrat'
            WHERE id NOT IN (
                SELECT DISTINCT client_id FROM contrats WHERE statut = 'en-cours'
            )
        ");

        // Clients inactifs — codes manquants ou partis
        DB::statement("
            UPDATE clients SET statut = 'inactif'
            WHERE nom_societe IN (
                'Business+Conseil',
                'COEXPAU',
                'M2S CREATIONS',
                'POMME D\'AMBRE',
                'Your Agency'
            )
        ");
    }

    public function down(): void
    {
        DB::statement("UPDATE clients SET statut = 'actif'");
    }
};
