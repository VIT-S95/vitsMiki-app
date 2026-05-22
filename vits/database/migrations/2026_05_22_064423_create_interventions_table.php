<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrat_id')->constrained()->onDelete('cascade');
            $table->date('date_intervention');
            $table->string('numero_bon_kizeo')->nullable();
            $table->enum('type', ['site', 'distance', 'flash'])->default('site');
            $table->integer('flash_numero')->nullable();
            $table->boolean('flash_consomme')->default(false);
            $table->integer('duree_minutes')->default(0);
            $table->string('motif')->nullable();
            $table->enum('statut', ['traitee', 'non-traitee'])->default('traitee');
            $table->enum('type_tri', ['standard', 'hors-contrat', 'piece-detachee', 'autre'])->default('standard');
            $table->boolean('source_kizeo')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};