<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Gallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'cover',
        'description',
        'items',
    ];

    protected $casts = [
        'items' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (Gallery $gallery) {
            if (! $gallery->slug || $gallery->isDirty('title')) {
                $gallery->slug = Str::slug($gallery->title) . '-' . Str::lower(Str::random(5));
            }
        });
    }

    public function getItemsCountAttribute(): int
    {
        return $this->sortedItems()
            ->filter(fn (array $item) => filled(data_get($item, 'image')))
            ->count();
    }

    public function sortedItems(): Collection
    {
        return collect($this->items ?? [])
            ->filter(fn ($item) => is_array($item))
            ->sortBy(fn (array $item) => (int) data_get($item, 'sort_order', 0))
            ->values();
    }
}
