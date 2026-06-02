<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kizeo_ignores', function (Blueprint $table) {
            $table->id();
            $table->string('kizeo_id');
            $table->string('form_id');
            $table->string('raison');
            $table->timestamps();

            $table->unique(['kizeo_id', 'form_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kizeo_ignores');
    }
};
