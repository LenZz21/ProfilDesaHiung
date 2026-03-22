<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(6);

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::lower(Str::random(5)),
            'category' => $this->faker->randomElement(['Pemerintahan', 'Pembangunan', 'Kesehatan', 'Pendidikan']),
            'excerpt' => $this->faker->paragraph(),
            'content' => collect(range(1, 4))->map(fn () => '<p>' . $this->faker->paragraph() . '</p>')->implode(''),
            'thumbnail' => null,
            'published_at' => now()->subDays($this->faker->numberBetween(1, 30)),
            'status' => 'published',
        ];
    }
}
