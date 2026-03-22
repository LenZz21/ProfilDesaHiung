<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PopulationSummaryStat extends Model
{
    use HasFactory;

    protected $touches = ['infographic'];

    protected $fillable = [
        'population_infographic_id',
        'label',
        'label_en',
        'value',
        'color',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'float',
            'sort_order' => 'integer',
        ];
    }

    public function infographic(): BelongsTo
    {
        return $this->belongsTo(PopulationInfographic::class, 'population_infographic_id');
    }
}
