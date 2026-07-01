<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boite_idees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('prenom');
            $table->enum('type', ['bug', 'modif', 'ajout']);
            $table->enum('priorite', ['normal', 'urgent', 'bas'])->default('normal');
            $table->enum('statut', ['en_attente', 'en_cours', 'fait', 'refuse'])->default('en_attente');
            $table->text('commentaire');
            $table->json('votes')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boite_idees');
    }
};
