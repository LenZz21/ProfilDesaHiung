<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('population_chart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('population_chart_section_id')
                ->constrained('population_chart_sections')
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
        Schema::dropIfExists('population_chart_items');
    }
};
