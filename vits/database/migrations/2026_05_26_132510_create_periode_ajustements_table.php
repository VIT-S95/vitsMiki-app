<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periode_ajustements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrat_id')->constrained()->onDelete('cascade');
            $table->integer('periode_numero');
            $table->date('periode_fin');
            $table->integer('solde_minutes'); // positif = crédit, négatif = dépassement
            $table->enum('action', ['reporte', 'ignore']);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('periode_ajustements');
    }
};