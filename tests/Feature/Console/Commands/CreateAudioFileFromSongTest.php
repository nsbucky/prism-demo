<?php
declare(strict_types=1);

use App\Jobs\CreateSongWithMureka;
use App\Models\Song;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    Queue::fake();
});

test('dispatches job to create song at mureka', function () {

    Queue::fake();

    $song = Song::factory()->create();

    $this->artisan('song:create', ['songId' => $song->id])
        ->expectsOutput("Song job dispatched for song ID: {$song->id}")
        ->assertExitCode(0);

    Queue::assertPushed(CreateSongWithMureka::class, function ($job) use ($song) {
        return $job->song->id === $song->id;
    });
});
