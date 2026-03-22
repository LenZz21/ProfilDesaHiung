<?php

namespace Database\Factories;

use App\Models\Gallery;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Gallery>
 */
class GalleryFactory extends Factory
{
    protected $model = Gallery::class;

    public function definition(): array
    {
        $title = 'Album ' . $this->faker->words(2, true);

        return [
            'title' => Str::title($title),
            'slug' => Str::slug($title) . '-' . Str::lower(Str::random(5)),
            'cover' => null,
            'description' => $this->faker->paragraph(),
            'items' => [],
        ];
    }
}
