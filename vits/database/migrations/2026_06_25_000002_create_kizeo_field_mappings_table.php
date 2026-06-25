<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kizeo_field_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('form_id');
            $table->string('kizeo_field');
            $table->string('app_field')->nullable();
            $table->string('transform')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['form_id', 'kizeo_field']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kizeo_field_mappings');
    }
};
