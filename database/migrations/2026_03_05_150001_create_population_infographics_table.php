<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('population_infographics', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('Infografis Penduduk');
            $table->string('subtitle', 500)->nullable();
            $table->text('hero_image')->nullable();
            $table->json('summary_stats')->nullable();
            $table->json('chart_sections')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('population_infographics');
    }
};
