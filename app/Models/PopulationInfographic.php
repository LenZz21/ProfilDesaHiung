<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class PopulationInfographic extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'title_en',
        'subtitle',
        'subtitle_en',
        'hero_image',
        'summary_stats',
        'chart_sections',
    ];

    protected function casts(): array
    {
        return [
            'summary_stats' => 'array',
            'chart_sections' => 'array',
        ];
    }

    public function getSummaryStatsCountAttribute(): int
    {
        if ($this->relationLoaded('summaryStats')) {
            return $this->summaryStats->count();
        }

        return $this->summaryStats()->count();
    }

    public function getChartSectionsCountAttribute(): int
    {
        if ($this->relationLoaded('chartSections')) {
            return $this->chartSections->count();
        }

        return $this->chartSections()->count();
    }

    public function getChartItemsCountAttribute(): int
    {
        return PopulationChartItem::query()
            ->whereIn(
                'population_chart_section_id',
                $this->chartSections()->pluck('id')
            )
            ->count();
    }

    public function summaryStats(): HasMany
    {
        return $this->hasMany(PopulationSummaryStat::class)->orderBy('sort_order');
    }

    public function chartSections(): HasMany
    {
        return $this->hasMany(PopulationChartSection::class)->orderBy('sort_order');
    }

    public static function defaultHeroImage(): string
    {
        return 'https://images.unsplash.com/photo-1518509562904-e7ef99cdcc86?auto=format&fit=crop&w=1800&q=80';
    }

    public function getHeroImageUrlAttribute(): string
    {
        $image = trim((string) $this->hero_image);

        if ($image === '') {
            return static::defaultHeroImage();
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        return Storage::url($image);
    }

    public static function defaultSummaryStats(): array
    {
        return [
            ['label' => 'Total Penduduk', 'value' => 440, 'color' => '#2563eb'],
            ['label' => 'Kepala Keluarga', 'value' => 170, 'color' => '#16a34a'],
            ['label' => 'Jumlah Dusun', 'value' => 3, 'color' => '#d97706'],
            ['label' => 'Laki-laki', 'value' => 224, 'color' => '#4f46e5'],
            ['label' => 'Perempuan', 'value' => 216, 'color' => '#e11d48'],
        ];
    }

    public static function defaultChartSections(): array
    {
        return [
            [
                'title' => 'Distribusi Per Lingkungan',
                'type' => 'bar',
                'items' => [
                    ['label' => 'Lingkungan 1', 'value' => 132, 'color' => '#2563eb'],
                    ['label' => 'Lingkungan 2', 'value' => 148, 'color' => '#10b981'],
                    ['label' => 'Lingkungan 3', 'value' => 160, 'color' => '#f59e0b'],
                ],
            ],
            [
                'title' => 'Komposisi Usia',
                'type' => 'bar',
                'items' => [
                    ['label' => 'Anak-anak (0-14)', 'value' => 92, 'color' => '#3b82f6'],
                    ['label' => 'Remaja (15-24)', 'value' => 80, 'color' => '#14b8a6'],
                    ['label' => 'Dewasa (25-54)', 'value' => 186, 'color' => '#f97316'],
                    ['label' => 'Lansia (55+)', 'value' => 82, 'color' => '#8b5cf6'],
                ],
            ],
            [
                'title' => 'Pendidikan',
                'type' => 'pie',
                'items' => [
                    ['label' => 'Tidak/Belum Sekolah', 'value' => 88, 'color' => '#3b82f6'],
                    ['label' => 'SD', 'value' => 134, 'color' => '#ef4444'],
                    ['label' => 'SMP', 'value' => 96, 'color' => '#22c55e'],
                    ['label' => 'SMA/SMK', 'value' => 92, 'color' => '#f59e0b'],
                    ['label' => 'Perguruan Tinggi', 'value' => 30, 'color' => '#8b5cf6'],
                ],
            ],
            [
                'title' => 'Pekerjaan Utama',
                'type' => 'bar',
                'items' => [
                    ['label' => 'Petani', 'value' => 126, 'color' => '#0ea5e9'],
                    ['label' => 'Nelayan', 'value' => 54, 'color' => '#06b6d4'],
                    ['label' => 'Wirausaha', 'value' => 76, 'color' => '#f59e0b'],
                    ['label' => 'PNS/TNI/Polri', 'value' => 21, 'color' => '#6366f1'],
                    ['label' => 'Lainnya', 'value' => 92, 'color' => '#94a3b8'],
                ],
            ],
            [
                'title' => 'Agama',
                'type' => 'doughnut',
                'items' => [
                    ['label' => 'Islam', 'value' => 360, 'color' => '#2563eb'],
                    ['label' => 'Kristen', 'value' => 80, 'color' => '#ef4444'],
                ],
            ],
            [
                'title' => 'Status Bangunan',
                'type' => 'bar',
                'items' => [
                    ['label' => 'Hak Milik', 'value' => 124, 'color' => '#2563eb'],
                    ['label' => 'Kontrak/Sewa', 'value' => 48, 'color' => '#10b981'],
                    ['label' => 'Rumah Dinas', 'value' => 12, 'color' => '#f59e0b'],
                    ['label' => 'Lainnya', 'value' => 18, 'color' => '#8b5cf6'],
                ],
            ],
            [
                'title' => 'Sumber Air Mandi',
                'type' => 'bar',
                'items' => [
                    ['label' => 'PDAM', 'value' => 150, 'color' => '#14b8a6'],
                    ['label' => 'Sumur', 'value' => 120, 'color' => '#22c55e'],
                    ['label' => 'Mata Air', 'value' => 26, 'color' => '#0ea5e9'],
                ],
            ],
            [
                'title' => 'Bantuan Sosial',
                'type' => 'bar',
                'items' => [
                    ['label' => 'PKH', 'value' => 72, 'color' => '#f59e0b'],
                    ['label' => 'BPNT', 'value' => 66, 'color' => '#f97316'],
                    ['label' => 'BLT-DD', 'value' => 32, 'color' => '#fb7185'],
                    ['label' => 'Lainnya', 'value' => 28, 'color' => '#38bdf8'],
                ],
            ],
            [
                'title' => 'Status Pernikahan',
                'type' => 'pie',
                'items' => [
                    ['label' => 'Belum Menikah', 'value' => 146, 'color' => '#3b82f6'],
                    ['label' => 'Menikah', 'value' => 230, 'color' => '#ef4444'],
                    ['label' => 'Cerai Hidup', 'value' => 22, 'color' => '#10b981'],
                    ['label' => 'Cerai Mati', 'value' => 42, 'color' => '#f59e0b'],
                ],
            ],
            [
                'title' => 'Kepemilikan Ijazah',
                'type' => 'bar',
                'items' => [
                    ['label' => 'Belum/Tidak Sekolah', 'value' => 74, 'color' => '#2563eb'],
                    ['label' => 'SD/Sederajat', 'value' => 126, 'color' => '#10b981'],
                    ['label' => 'SMP/Sederajat', 'value' => 98, 'color' => '#f59e0b'],
                    ['label' => 'SMA/Perguruan Tinggi', 'value' => 142, 'color' => '#8b5cf6'],
                ],
            ],
        ];
    }
}
