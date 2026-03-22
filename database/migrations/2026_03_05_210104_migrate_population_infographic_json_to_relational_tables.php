<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $infographics = DB::table('population_infographics')
            ->select(['id', 'summary_stats', 'chart_sections', 'created_at', 'updated_at'])
            ->get();

        foreach ($infographics as $infographic) {
            $createdAt = $infographic->created_at ?: now();
            $updatedAt = $infographic->updated_at ?: now();

            $summaryStats = json_decode($infographic->summary_stats ?: '[]', true);

            if (is_array($summaryStats)) {
                foreach ($summaryStats as $index => $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    DB::table('population_summary_stats')->insert([
                        'population_infographic_id' => $infographic->id,
                        'label' => (string) data_get($item, 'label', '-'),
                        'value' => (float) data_get($item, 'value', 0),
                        'color' => (string) data_get($item, 'color', '#2563eb'),
                        'sort_order' => $index + 1,
                        'created_at' => $createdAt,
                        'updated_at' => $updatedAt,
                    ]);
                }
            }

            $chartSections = json_decode($infographic->chart_sections ?: '[]', true);

            if (! is_array($chartSections)) {
                continue;
            }

            foreach ($chartSections as $sectionIndex => $section) {
                if (! is_array($section)) {
                    continue;
                }

                $sectionId = DB::table('population_chart_sections')->insertGetId([
                    'population_infographic_id' => $infographic->id,
                    'title' => (string) data_get($section, 'title', 'Grafik ' . ($sectionIndex + 1)),
                    'type' => (string) data_get($section, 'type', 'bar'),
                    'sort_order' => $sectionIndex + 1,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);

                $items = data_get($section, 'items', []);

                if (! is_array($items)) {
                    continue;
                }

                foreach ($items as $itemIndex => $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    DB::table('population_chart_items')->insert([
                        'population_chart_section_id' => $sectionId,
                        'label' => (string) data_get($item, 'label', '-'),
                        'value' => (float) data_get($item, 'value', 0),
                        'color' => (string) data_get($item, 'color', '#2563eb'),
                        'sort_order' => $itemIndex + 1,
                        'created_at' => $createdAt,
                        'updated_at' => $updatedAt,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        DB::table('population_chart_items')->delete();
        DB::table('population_chart_sections')->delete();
        DB::table('population_summary_stats')->delete();
    }
};
