<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE clients MODIFY COLUMN statut ENUM('actif', 'sans_contrat', 'inactif') NOT NULL DEFAULT 'sans_contrat'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE clients MODIFY COLUMN statut ENUM('actif', 'inactif') NOT NULL DEFAULT 'actif'");
    }
};
