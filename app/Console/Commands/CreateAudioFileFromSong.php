<?php

namespace App\Console\Commands;

use App\Jobs\CreateSongWithMureka;
use App\Models\Song;
use Illuminate\Console\Command;

class CreateAudioFileFromSong extends Command
{
    protected $signature = 'song:create {songId : The ID of the song}';

    protected $description = 'Use Mureka to create an audio file from a song';

    public function handle() : int
    {
        $song = Song::findOrFail($this->argument('songId'));

        $this->components->task('Dispatching job...', function () use ($song) {
            CreateSongWithMureka::dispatch($song);
        });

        $this->info("Song job dispatched for song ID: {$song->id}");

        return self::SUCCESS;
    }
}
