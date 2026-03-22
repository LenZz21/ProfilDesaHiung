<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $title = 'Kegiatan ' . $this->faker->sentence(3);
        $start = now()->addDays($this->faker->numberBetween(1, 40));

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::lower(Str::random(5)),
            'description' => collect(range(1, 3))->map(fn () => '<p>' . $this->faker->paragraph() . '</p>')->implode(''),
            'start_at' => $start,
            'end_at' => (clone $start)->addHours(3),
            'location' => $this->faker->randomElement(['Balai Desa', 'Lapangan Desa', 'Kantor Desa']),
            'banner' => null,
            'status' => 'published',
        ];
    }
}
