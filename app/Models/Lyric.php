<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lyric extends Model
{
    /** @use HasFactory<\Database\Factories\LyricFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'embedding',
        'original_text',
    ];

    protected $connection = 'pgsql';

    protected $casts = [
        'embedding' => 'array',
    ];
}
