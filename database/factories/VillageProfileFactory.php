<?php

namespace Database\Factories;

use App\Models\VillageProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VillageProfile>
 */
class VillageProfileFactory extends Factory
{
    protected $model = VillageProfile::class;

    public function definition(): array
    {
        return [
            'name' => 'Kampung Hiung',
            'address' => 'Jl. Raya Hiung No. 1, Kecamatan Maju Jaya, Kabupaten Sejahtera',
            'about' => 'Kampung Hiung adalah desa agraris dengan potensi pertanian, UMKM, dan pariwisata lokal.',
            'vision' => 'Terwujudnya desa maju, mandiri, dan sejahtera berbasis gotong royong.',
            'mission' => "1. Meningkatkan kualitas pelayanan publik.\n2. Menguatkan ekonomi desa.\n3. Memperkuat tata kelola pemerintahan yang transparan.",
            'history' => 'Kampung Hiung berdiri sejak tahun 1945 dan terus berkembang melalui semangat gotong royong masyarakat.',
            'map_embed' => 'https://www.google.com/maps?q=-6.200000,106.816666&output=embed',
            'whatsapp' => '6281234567890',
            'email' => 'desa@hiung.id',
            'facebook_url' => 'https://facebook.com/kampunghiung',
            'instagram_url' => 'https://instagram.com/kampunghiung',
            'x_url' => 'https://x.com/kampunghiung',
        ];
    }
}
