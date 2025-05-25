<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class MurekaCreation extends Model
{
    /** @use HasFactory<\Database\Factories\MurekaCreationFactory> */
    use HasFactory;

    protected $fillable = [
        'song_id',
        'mureka_id',
        'model',
        'status',
        'failed_reason',
        'finished_at',
        'failed_at',
        'choices'
    ];

    protected $casts = [
        'choices'     => 'json',
        'finished_at' => 'datetime',
        'failed_at'   => 'datetime'
    ];

    public function song()
    {
        return $this->belongsTo(Song::class);
    }

    public function downloadableSongs() : Collection
    {
        return collect($this->choices)
            ->filter(fn (array $choice) => isset($choice['url']))
            ->map(fn (array $choice) => [
                'url'      => $choice['url'],
                'flac_url' => $choice['flac_url'] ?? null,
                'duration' => $choice['duration'] ?? null,
            ]);
    }
}
