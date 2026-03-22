<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeOverviewTranslation extends Model
{
    protected $fillable = [
        'locale',
        'about',
    ];

    public static function aboutForLocale(string $locale): ?string
    {
        return static::query()
            ->where('locale', $locale)
            ->value('about');
    }
}
