<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE interventions MODIFY COLUMN type ENUM('site','distance','flash','ajustement') NOT NULL DEFAULT 'site'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE interventions MODIFY COLUMN type ENUM('site','distance','flash') NOT NULL DEFAULT 'site'");
    }
};
