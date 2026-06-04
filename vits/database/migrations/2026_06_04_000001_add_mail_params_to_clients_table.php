<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('mail_alerte_fin_contrat')->default(false)->after('statut');
            $table->boolean('mail_rapport_periodique')->default(false)->after('mail_alerte_fin_contrat');
            $table->enum('mail_frequence', ['mensuel', 'hebdo', 'trimestriel'])->default('mensuel')->nullable()->after('mail_rapport_periodique');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['mail_alerte_fin_contrat', 'mail_rapport_periodique', 'mail_frequence']);
        });
    }
};
