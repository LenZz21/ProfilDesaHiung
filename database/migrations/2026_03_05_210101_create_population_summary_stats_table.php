<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('population_summary_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('population_infographic_id')
                ->constrained('population_infographics')
                ->cascadeOnDelete();
            $table->string('label');
            $table->decimal('value', 12, 2)->default(0);
            $table->string('color', 20)->default('#2563eb');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('population_summary_stats');
    }
};
