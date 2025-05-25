<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;

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

    public function murekaCreations()
    {
        return $this->hasMany(MurekaCreation::class);
    }
}
