<?php

namespace Database\Factories;

use App\Models\Official;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Official>
 */
class OfficialFactory extends Factory
{
    protected $model = Official::class;

    public function definition(): array
    {
        $position = $this->faker->randomElement(['Kepala Desa', 'Sekretaris Desa', 'Kasi Pemerintahan', 'Kasi Pelayanan', 'Kaur Keuangan']);

        return [
            'name' => $this->faker->name(),
            'position' => $position,
            'structure_group' => Official::detectStructureGroupFromPosition($position),
            'photo' => null,
            'phone' => '08' . $this->faker->numerify('##########'),
            'instagram_url' => $this->faker->optional()->url(),
            'facebook_url' => $this->faker->optional()->url(),
            'sort_order' => $this->faker->numberBetween(1, 10),
            'is_active' => true,
        ];
    }
}
