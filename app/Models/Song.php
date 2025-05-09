<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    protected $fillable = [
        'title',
        'keywords',
        'lyrics',
        'prompt',
        'formatted_prompt',
        'matched_lyrics'
    ];

    protected $casts = [
        'matched_lyrics' => 'json'
    ];
}
