<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('population_infographics', function (Blueprint $table) {
            $table->string('title_en')->nullable();
            $table->string('subtitle_en', 500)->nullable();
        });

        Schema::table('population_summary_stats', function (Blueprint $table) {
            $table->string('label_en')->nullable();
        });

        Schema::table('population_chart_sections', function (Blueprint $table) {
            $table->string('title_en')->nullable();
        });

        Schema::table('population_chart_items', function (Blueprint $table) {
            $table->string('label_en')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('population_chart_items', function (Blueprint $table) {
            $table->dropColumn('label_en');
        });

        Schema::table('population_chart_sections', function (Blueprint $table) {
            $table->dropColumn('title_en');
        });

        Schema::table('population_summary_stats', function (Blueprint $table) {
            $table->dropColumn('label_en');
        });

        Schema::table('population_infographics', function (Blueprint $table) {
            $table->dropColumn([
                'title_en',
                'subtitle_en',
            ]);
        });
    }
};
