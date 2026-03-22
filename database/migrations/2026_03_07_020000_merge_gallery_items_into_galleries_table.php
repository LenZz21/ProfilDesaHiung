<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('galleries') && ! Schema::hasColumn('galleries', 'items')) {
            Schema::table('galleries', function (Blueprint $table) {
                $table->json('items')->nullable()->after('description');
            });
        }

        if (! Schema::hasTable('gallery_items')) {
            return;
        }

        $itemsPerGallery = DB::table('gallery_items')
            ->orderBy('gallery_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('gallery_id');

        foreach ($itemsPerGallery as $galleryId => $items) {
            $normalizedItems = $items
                ->map(fn ($item) => [
                    'image' => (string) $item->image,
                    'caption' => $item->caption !== null ? (string) $item->caption : null,
                    'sort_order' => (int) ($item->sort_order ?? 0),
                ])
                ->values()
                ->all();

            DB::table('galleries')
                ->where('id', $galleryId)
                ->update([
                    'items' => json_encode($normalizedItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => now(),
                ]);
        }

        Schema::dropIfExists('gallery_items');
    }

    public function down(): void
    {
        if (! Schema::hasTable('gallery_items')) {
            Schema::create('gallery_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('gallery_id')->constrained()->cascadeOnDelete();
                $table->string('image');
                $table->string('caption')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('galleries') || ! Schema::hasColumn('galleries', 'items')) {
            return;
        }

        $galleries = DB::table('galleries')->select(['id', 'items', 'created_at', 'updated_at'])->get();

        foreach ($galleries as $gallery) {
            $decodedItems = json_decode((string) ($gallery->items ?? '[]'), true);

            if (! is_array($decodedItems)) {
                continue;
            }

            foreach ($decodedItems as $item) {
                if (! is_array($item) || ! filled(data_get($item, 'image'))) {
                    continue;
                }

                DB::table('gallery_items')->insert([
                    'gallery_id' => $gallery->id,
                    'image' => (string) data_get($item, 'image'),
                    'caption' => data_get($item, 'caption'),
                    'sort_order' => (int) data_get($item, 'sort_order', 0),
                    'created_at' => $gallery->created_at,
                    'updated_at' => $gallery->updated_at,
                ]);
            }
        }

        Schema::table('galleries', function (Blueprint $table) {
            $table->dropColumn('items');
        });
    }
};
