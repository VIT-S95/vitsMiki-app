<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite ne supporte pas ALTER COLUMN sur enum
        // On recrée la colonne via une migration de données
        DB::statement("UPDATE interventions SET type = 'site' WHERE type NOT IN ('site','distance','flash','administrateur')");
    }
    public function down(): void {}
};