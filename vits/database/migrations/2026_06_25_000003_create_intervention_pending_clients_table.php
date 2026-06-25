<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intervention_pending_clients', function (Blueprint $table) {
            $table->id();
            $table->string('nom_saisi');
            $table->string('form_id');
            $table->string('numero_bon_kizeo');
            $table->json('raw_data');
            $table->enum('statut', ['en_attente', 'rattache', 'ignore'])->default('en_attente');
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intervention_pending_clients');
    }
};
