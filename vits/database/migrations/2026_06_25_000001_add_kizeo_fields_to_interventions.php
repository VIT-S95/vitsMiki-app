<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->text('commentaires')->nullable()->after('notes');
            $table->text('pieces_detachees')->nullable()->after('commentaires');
            $table->text('demande_annexe')->nullable()->after('pieces_detachees');
            $table->boolean('hors_heure_ouvree')->default(false)->after('demande_annexe');
            $table->string('donneur_ordre')->nullable()->after('hors_heure_ouvree');
            $table->string('n_ticket')->nullable()->after('donneur_ordre');
            $table->string('n_devis')->nullable()->after('n_ticket');
            $table->json('raw_data')->nullable()->after('n_devis');
        });
    }

    public function down(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->dropColumn([
                'commentaires',
                'pieces_detachees',
                'demande_annexe',
                'hors_heure_ouvree',
                'donneur_ordre',
                'n_ticket',
                'n_devis',
                'raw_data',
            ]);
        });
    }
};
