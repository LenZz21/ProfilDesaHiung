<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LetterSubmission extends Model
{
    use HasFactory;

    public const STATUS_BARU = 'baru';
    public const STATUS_DITERIMA = 'diterima';
    public const STATUS_DIPROSES = 'diproses';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DITOLAK = 'ditolak';

    protected $fillable = [
        'service_type',
        'service_name',
        'full_name',
        'nik',
        'whatsapp',
        'email',
        'purpose',
        'status',
        'admin_notes',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_BARU => 'Baru',
            self::STATUS_DITERIMA => 'Diterima',
            self::STATUS_DIPROSES => 'Diproses',
            self::STATUS_SELESAI => 'Selesai',
            self::STATUS_DITOLAK => 'Ditolak',
        ];
    }
}
