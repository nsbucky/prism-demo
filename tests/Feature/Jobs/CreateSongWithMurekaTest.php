<?php

declare(strict_types=1);

use App\Jobs\CheckMurekaSongStatus;
use App\Jobs\CreateSongWithMureka;
use App\Models\Song;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    Queue::fake();
});

it('handles successful response with succeeded status', function () {
    Http::fake([
        'https://api.mureka.io/v1/song/generate' => Http::response([
            'id' => 'song-123',
            'created_at' => time(),
            'finished_at' => time() + 30,
            'model' => 'auto',
            'status' => 'succeeded',
            'failed_reason' => null,
            'choices' => [
                [
                    'index' => 0,
                    'url' => 'https://example.com/song.mp3',
                    'flac_url' => 'https://example.com/song.flac',
                    'duration' => 120,
                    'lyrics_sections' => [],
                ],
            ],
        ], 200),
    ]);

    $song = Song::create([
        'title' => 'Test Song',
        'keywords' => 'test, song',
        'lyrics' => 'This is a test song',
        'prompt' => 'Test prompt',
        'formatted_prompt' => 'Formatted test prompt',
        'matched_lyrics' => [],
    ]);

    $job = new CreateSongWithMureka($song);
    $job->handle();

    Queue::assertNotPushed(CheckMurekaSongStatus::class);
});

it('handles failed HTTP response', function () {
    Http::fake([
        'https://api.mureka.io/v1/song/generate' => Http::response([
            'error' => 'Invalid request',
        ], 400),
    ]);

    $song = Song::create([
        'title' => 'Test Song',
        'keywords' => 'test, song',
        'lyrics' => 'This is a test song',
        'prompt' => 'Test prompt',
        'formatted_prompt' => 'Formatted test prompt',
        'matched_lyrics' => [],
    ]);

    $job = new CreateSongWithMureka($song);
    $job->handle();

    Queue::assertNotPushed(CheckMurekaSongStatus::class);
});

it('handles failed status in response', function ($status) {
    Http::fake([
        'https://api.mureka.io/v1/song/generate' => Http::response([
            'id' => 'song-123',
            'created_at' => time(),
            'finished_at' => time() + 30,
            'model' => 'auto',
            'status' => $status,
            'failed_reason' => 'Something went wrong',
            'choices' => [],
        ], 200),
    ]);

    $song = Song::create([
        'title' => 'Test Song',
        'keywords' => 'test, song',
        'lyrics' => 'This is a test song',
        'prompt' => 'Test prompt',
        'formatted_prompt' => 'Formatted test prompt',
        'matched_lyrics' => [],
    ]);

    $job = new CreateSongWithMureka($song);
    $job->handle();

    Queue::assertNotPushed(CheckMurekaSongStatus::class);
})->with(['failed', 'timeouted', 'canceled']);

it('dispatches CheckMurekaSongStatus job for pending statuses', function ($status) {
    Http::fake([
        'https://api.mureka.io/v1/song/generate' => Http::response([
            'id' => 'song-123',
            'created_at' => time(),
            'finished_at' => null,
            'model' => 'auto',
            'status' => $status,
            'failed_reason' => null,
            'choices' => [],
        ], 200),
    ]);

    $song = Song::create([
        'title' => 'Test Song',
        'keywords' => 'test, song',
        'lyrics' => 'This is a test song',
        'prompt' => 'Test prompt',
        'formatted_prompt' => 'Formatted test prompt',
        'matched_lyrics' => [],
    ]);

    $job = new CreateSongWithMureka($song);
    $job->handle();

    Queue::assertPushed(CheckMurekaSongStatus::class);

    $this->assertDatabaseHas('mureka_creations', [
        'song_id' => $song->id,
        'mureka_id' => 'song-123',
        'model' => 'auto',
        'status' => $status,
        'failed_reason' => null,
    ]);

})->with(['preparing', 'running', 'queued']);

it('sends correct request to Mureka API', function () {
    config(['services.mureka.api_key' => 'test']);

    Http::fake([
        'https://api.mureka.io/v1/song/generate' => Http::response([
            'id' => 'song-123',
            'status' => 'succeeded',
            'choices' => [],
        ], 200),
    ]);

    $song = Song::create([
        'title' => 'Test Song',
        'keywords' => 'test, song',
        'lyrics' => 'This is a test song lyrics',
        'prompt' => 'Test prompt',
        'formatted_prompt' => 'Formatted test prompt',
        'matched_lyrics' => [],
    ]);

    $job = new CreateSongWithMureka($song);
    $job->handle();

    Http::assertSent(function ($request) use ($song) {
        return $request->url() === 'https://api.mureka.io/v1/song/generate' &&
               $request->method() === 'POST' &&
               $request->header('Authorization')[0] === 'Bearer '.config('services.mureka.api_key') &&
               $request->data()['lyrics'] === $song->lyrics &&
               $request->data()['model'] === 'auto' &&
               $request->data()['prompt'] === 'male vocal, polka, upbeat, happy';
    });
});
