<?php

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        $title = 'Dokumen ' . $this->faker->words(3, true);

        return [
            'title' => Str::title($title),
            'slug' => Str::slug($title) . '-' . Str::lower(Str::random(5)),
            'category' => $this->faker->randomElement(['Perdes', 'Laporan', 'Transparansi', 'Pengumuman']),
            'file' => 'seed/documents/placeholder.txt',
            'description' => $this->faker->paragraph(),
            'published_at' => now()->subDays($this->faker->numberBetween(1, 15)),
            'status' => 'published',
        ];
    }
}
