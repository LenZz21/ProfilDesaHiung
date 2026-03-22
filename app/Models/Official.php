<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Official extends Model
{
    use HasFactory;

    public const GROUP_LEADER = 'leader';
    public const GROUP_SECRETARY = 'secretary';
    public const GROUP_SECTION_HEADS = 'section_heads';
    public const GROUP_KAUR = 'kaur';
    public const GROUP_HEAD_LINDONGANG = 'head_lindongang';
    public const GROUP_OTHER = 'other';

    protected $fillable = [
        'name',
        'position',
        'structure_group',
        'section_title',
        'photo',
        'phone',
        'instagram_url',
        'facebook_url',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function structureGroupOptions(): array
    {
        return [
            self::GROUP_LEADER => 'Kepala Desa',
            self::GROUP_SECRETARY => 'Sekretaris Desa',
            self::GROUP_SECTION_HEADS => 'Kepala Seksi',
            self::GROUP_KAUR => 'Kaur',
            self::GROUP_HEAD_LINDONGANG => 'Kepala Lindongang',
            self::GROUP_OTHER => 'Lainnya',
        ];
    }

    public static function detectStructureGroupFromPosition(?string $position): string
    {
        $position = strtolower(trim((string) $position));

        if (str_contains($position, 'kepala desa') || str_contains($position, 'kapitalaung')) {
            return self::GROUP_LEADER;
        }

        if (str_contains($position, 'sekretaris')) {
            return self::GROUP_SECRETARY;
        }

        if (str_contains($position, 'kasi') || str_contains($position, 'seksi')) {
            return self::GROUP_SECTION_HEADS;
        }

        if (str_contains($position, 'kaur')) {
            return self::GROUP_KAUR;
        }

        if (str_contains($position, 'lindong')) {
            return self::GROUP_HEAD_LINDONGANG;
        }

        return self::GROUP_OTHER;
    }
}
