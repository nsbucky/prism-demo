<?php

declare(strict_types=1);

use App\Jobs\CheckMurekaSongStatus;
use App\Jobs\DownloadMurekaSong;
use App\Models\MurekaCreation;
use App\Models\Song;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    Queue::fake();
});

it('handles successful status and dispatches download job', function () {
    config(['services.mureka.api_key' => 'test-key']);
    
    $song = Song::factory()->create();
    $murekaCreation = MurekaCreation::factory()->create([
        'song_id' => $song->id,
        'mureka_id' => 'song-123',
        'status' => 'queued'
    ]);

    Http::fake([
        'https://api.mureka.ai/v1/song/query/song-123' => Http::response([
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
                    'lyrics_sections' => []
                ]
            ]
        ], 200)
    ]);

    $job = new CheckMurekaSongStatus($murekaCreation);
    $job->handle();

    Queue::assertPushed(DownloadMurekaSong::class, function ($job) use ($murekaCreation) {
        return $job->murekaCreation->id === $murekaCreation->id;
    });

    $this->assertDatabaseHas('mureka_creations', [
        'id' => $murekaCreation->id,
        'mureka_status' => 'succeeded',
        'mureka_url' => 'https://example.com/song.mp3',
        'mureka_flac_url' => 'https://example.com/song.flac',
        'mureka_duration' => 120
    ]);
});

it('handles failed HTTP response', function () {
    config(['services.mureka.api_key' => 'test-key']);
    
    $song = Song::factory()->create();
    $murekaCreation = MurekaCreation::factory()->create([
        'song_id' => $song->id,
        'mureka_id' => 'song-123',
        'status' => 'queued'
    ]);

    Http::fake([
        'https://api.mureka.ai/v1/song/query/song-123' => Http::response([
            'error' => 'Not found'
        ], 404)
    ]);

    $job = new CheckMurekaSongStatus($murekaCreation);
    $job->handle();

    Queue::assertNotPushed(DownloadMurekaSong::class);
    Queue::assertNotPushed(CheckMurekaSongStatus::class);
});

it('handles failed status in response', function ($status) {
    config(['services.mureka.api_key' => 'test-key']);
    
    $song = Song::factory()->create();
    $murekaCreation = MurekaCreation::factory()->create([
        'song_id' => $song->id,
        'mureka_id' => 'song-123',
        'status' => 'queued'
    ]);

    Http::fake([
        'https://api.mureka.ai/v1/song/query/song-123' => Http::response([
            'id' => 'song-123',
            'created_at' => time(),
            'finished_at' => time() + 30,
            'model' => 'auto',
            'status' => $status,
            'failed_reason' => 'Something went wrong',
            'choices' => []
        ], 200)
    ]);

    $job = new CheckMurekaSongStatus($murekaCreation);
    $job->handle();

    Queue::assertNotPushed(DownloadMurekaSong::class);
    Queue::assertNotPushed(CheckMurekaSongStatus::class);
})->with(['failed', 'timeouted', 'canceled']);

it('reschedules itself for pending statuses', function ($status) {
    config(['services.mureka.api_key' => 'test-key']);
    
    $song = Song::factory()->create();
    $murekaCreation = MurekaCreation::factory()->create([
        'song_id' => $song->id,
        'mureka_id' => 'song-123',
        'status' => 'queued'
    ]);

    Http::fake([
        'https://api.mureka.ai/v1/song/query/song-123' => Http::response([
            'id' => 'song-123',
            'created_at' => time(),
            'finished_at' => null,
            'model' => 'auto',
            'status' => $status,
            'failed_reason' => null,
            'choices' => []
        ], 200)
    ]);

    $job = new CheckMurekaSongStatus($murekaCreation);
    $job->handle();

    Queue::assertPushed(CheckMurekaSongStatus::class, function ($job) use ($murekaCreation) {
        return $job->murekaCreation->id === $murekaCreation->id &&
               $job->delay->greaterThan(now()->addSeconds(55));
    });

    Queue::assertNotPushed(DownloadMurekaSong::class);
})->with(['preparing', 'running', 'queued']);

it('sends correct request to Mureka API', function () {
    config(['services.mureka.api_key' => 'test-api-key']);
    
    $song = Song::factory()->create();
    $murekaCreation = MurekaCreation::factory()->create([
        'song_id' => $song->id,
        'mureka_id' => 'song-test-123',
        'status' => 'queued'
    ]);

    Http::fake([
        '*' => Http::response([
            'id' => 'song-test-123',
            'status' => 'succeeded',
            'choices' => [
                [
                    'url' => 'https://example.com/song.mp3',
                    'flac_url' => 'https://example.com/song.flac',
                    'duration' => 120
                ]
            ]
        ], 200)
    ]);

    $job = new CheckMurekaSongStatus($murekaCreation);
    $job->handle();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.mureka.ai/v1/song/query/song-test-123' &&
               $request->method() === 'GET' &&
               $request->header('Authorization')[0] === 'Bearer test-api-key';
    });
});

it('handles HTTP timeout', function () {
    config(['services.mureka.api_key' => 'test-key']);
    
    $song = Song::factory()->create();
    $murekaCreation = MurekaCreation::factory()->create([
        'song_id' => $song->id,
        'mureka_id' => 'song-123',
        'status' => 'queued'
    ]);

    Http::fake([
        'https://api.mureka.ai/v1/song/query/song-123' => Http::timeout()
    ]);

    $job = new CheckMurekaSongStatus($murekaCreation);
    $job->handle();

    Queue::assertNotPushed(DownloadMurekaSong::class);
    Queue::assertNotPushed(CheckMurekaSongStatus::class);
});