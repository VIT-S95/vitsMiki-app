<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('titre');
            $table->string('numero_contrat_vits')->nullable();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->integer('duree_mois')->default(12);
            $table->integer('duree_periode_mois')->default(12);
            $table->integer('heures_par_periode')->default(10);
            $table->integer('numero_renouvellement')->default(0);
            $table->enum('statut', ['non-actif', 'en-cours', 'expire'])->default('non-actif');
            $table->string('pdf_contrat')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrats');
    }
};