<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->boolean('deductible')->default(true)->after('source_kizeo');
        });
    }
    public function down(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->dropColumn('deductible');
        });
    }
};