<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PopulationChartSection extends Model
{
    use HasFactory;

    protected $touches = ['infographic'];

    protected $fillable = [
        'population_infographic_id',
        'title',
        'title_en',
        'type',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function infographic(): BelongsTo
    {
        return $this->belongsTo(PopulationInfographic::class, 'population_infographic_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PopulationChartItem::class)->orderBy('sort_order');
    }
}
