<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PopulationChartItem extends Model
{
    use HasFactory;

    protected $touches = ['section'];

    protected $fillable = [
        'population_chart_section_id',
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

    public function section(): BelongsTo
    {
        return $this->belongsTo(PopulationChartSection::class, 'population_chart_section_id');
    }
}
