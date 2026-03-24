<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Event;
use App\Models\Gallery;
use App\Models\Official;
use App\Models\OfficialStructureTitle;
use App\Models\PopulationInfographic;
use App\Models\Post;
use App\Models\User;
use App\Models\VillageProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Storage::disk('public')->makeDirectory('seed/images');
        Storage::disk('public')->makeDirectory('seed/documents');

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="800"><rect width="100%" height="100%" fill="#0f766e"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#ffffff" font-size="44">Profil Desa</text></svg>';

        VillageProfile::factory()->create();

        // User::updateOrCreate(
        //     ['email' => 'admin@desa.test'],
        //     [
        //         'name' => 'Admin Desa',
        //         'password' => Hash::make('password'),
        //     ]
        // );

        $super_admin = User::where('email','superadmin@local.com')->first();

        if(empty($super_admin)){
            User::create([
                'name' => 'Super Admin',
                'email' => 'superadmin@local.com',
                'password' => Hash::make('12345678'),
                'role' => 'super_admin',
            ]);
        }

        PopulationInfographic::updateOrCreate(
            ['title' => 'Infografis Penduduk'],
            [
                'title_en' => 'Population Infographics',
                'subtitle' => 'Statistik kependudukan kampung yang terintegrasi dan transparan.',
                'subtitle_en' => 'Integrated and transparent village population statistics.',
                'hero_image' => PopulationInfographic::defaultHeroImage(),
                'summary_stats' => PopulationInfographic::defaultSummaryStats(),
                'chart_sections' => PopulationInfographic::defaultChartSections(),
            ]
        );

        OfficialStructureTitle::query()->firstOrCreate(
            [],
            OfficialStructureTitle::defaults()
        );

        Official::factory(6)->create()->each(function (Official $official, int $index) use ($svg) {
            $path = "seed/images/official-{$index}.svg";
            Storage::disk('public')->put($path, $svg);
            $official->update([
                'photo' => $path,
                'sort_order' => $index + 1,
                'structure_group' => Official::detectStructureGroupFromPosition($official->position),
            ]);
        });

        Post::factory(6)->create()->each(function (Post $post, int $index) use ($svg) {
            $path = "seed/images/post-{$index}.svg";
            Storage::disk('public')->put($path, $svg);
            $post->update(['thumbnail' => $path]);
        });

        Event::factory(4)->create()->each(function (Event $event, int $index) use ($svg) {
            $path = "seed/images/event-{$index}.svg";
            Storage::disk('public')->put($path, $svg);
            $event->update(['banner' => $path]);
        });

        Gallery::factory(2)->create()->each(function (Gallery $gallery, int $galleryIndex) use ($svg) {
            $coverPath = "seed/images/gallery-cover-{$galleryIndex}.svg";
            Storage::disk('public')->put($coverPath, $svg);

            $items = [];

            foreach (range(1, 6) as $itemIndex) {
                $imagePath = "seed/images/gallery-{$galleryIndex}-item-{$itemIndex}.svg";
                Storage::disk('public')->put($imagePath, $svg);

                $items[] = [
                    'image' => $imagePath,
                    'caption' => "Dokumentasi {$itemIndex}",
                    'sort_order' => $itemIndex,
                ];
            }

            $gallery->update([
                'cover' => $coverPath,
                'items' => $items,
            ]);
        });

        Document::factory(6)->create()->each(function (Document $document, int $index) {
            $path = "seed/documents/dokumen-{$index}.txt";
            Storage::disk('public')->put($path, "Dokumen publikasi desa #{$index}");
            $document->update(['file' => $path]);
        });
    }
}
