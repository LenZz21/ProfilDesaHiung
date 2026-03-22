<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('population_chart_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('population_infographic_id')
                ->constrained('population_infographics')
                ->cascadeOnDelete();
            $table->string('title');
            $table->string('type', 20)->default('bar');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('population_chart_sections');
    }
};
