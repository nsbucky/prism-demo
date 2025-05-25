<?php

declare(strict_types=1);

use App\Jobs\DownloadMurekaSong;
use App\Models\MurekaCreation;
use App\Models\Song;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

it('downloads songs from mureka creation choices', function () {
    $song = Song::factory()->create();
    $murekaCreation = MurekaCreation::factory()->create([
        'song_id' => $song->id,
        'mureka_id' => 'song-123',
        'status' => 'succeeded',
        'choices' => [
            [
                'index' => 0,
                'url' => 'https://example.com/song1.mp3',
                'flac_url' => 'https://example.com/song1.flac',
                'duration' => 120
            ],
            [
                'index' => 1,
                'url' => 'https://example.com/song2.mp3',
                'flac_url' => 'https://example.com/song2.flac',
                'duration' => 130
            ]
        ]
    ]);

    Http::fake([
        'https://example.com/song1.mp3' => Http::response('mp3 content 1', 200),
        'https://example.com/song2.mp3' => Http::response('mp3 content 2', 200)
    ]);

    $job = new DownloadMurekaSong($murekaCreation);
    $job->handle();

    Storage::disk('public')->assertExists("songs/{$song->id}/song1.mp3");
    Storage::disk('public')->assertExists("songs/{$song->id}/song2.mp3");

    expect(Storage::disk('public')->get("songs/{$song->id}/song1.mp3"))->toBe('mp3 content 1');
    expect(Storage::disk('public')->get("songs/{$song->id}/song2.mp3"))->toBe('mp3 content 2');
});

it('skips download if file already exists', function () {
    $song = Song::factory()->create();
    $murekaCreation = MurekaCreation::factory()->create([
        'song_id' => $song->id,
        'mureka_id' => 'song-123',
        'status' => 'succeeded',
        'choices' => [
            [
                'index' => 0,
                'url' => 'https://example.com/song1.mp3',
                'flac_url' => 'https://example.com/song1.flac',
                'duration' => 120
            ]
        ]
    ]);

    // Pre-create the file
    Storage::disk('public')->put("songs/{$song->id}/song1.mp3", 'existing content');

    Http::fake();

    $job = new DownloadMurekaSong($murekaCreation);
    $job->handle();

    // Verify no HTTP requests were made
    Http::assertNothingSent();

    // Verify file still has original content
    expect(Storage::disk('public')->get("songs/{$song->id}/song1.mp3"))->toBe('existing content');
});

it('creates directory if it does not exist', function () {
    $song = Song::factory()->create();
    $murekaCreation = MurekaCreation::factory()->create([
        'song_id' => $song->id,
        'mureka_id' => 'song-123',
        'status' => 'succeeded',
        'choices' => [
            [
                'index' => 0,
                'url' => 'https://example.com/song.mp3',
                'flac_url' => 'https://example.com/song.flac',
                'duration' => 120
            ]
        ]
    ]);

    Http::fake([
        'https://example.com/song.mp3' => Http::response('mp3 content', 200)
    ]);

    Storage::disk('public')->assertMissing("songs/{$song->id}");

    $job = new DownloadMurekaSong($murekaCreation);
    $job->handle();

    Storage::disk('public')->assertExists("songs/{$song->id}");
    Storage::disk('public')->assertExists("songs/{$song->id}/song.mp3");
});

it('handles failed download response', function () {
    $song = Song::factory()->create();
    $murekaCreation = MurekaCreation::factory()->create([
        'song_id' => $song->id,
        'mureka_id' => 'song-123',
        'status' => 'succeeded',
        'choices' => [
            [
                'index' => 0,
                'url' => 'https://example.com/song.mp3',
                'flac_url' => 'https://example.com/song.flac',
                'duration' => 120
            ]
        ]
    ]);

    Http::fake([
        'https://example.com/song.mp3' => Http::response('Not Found', 404)
    ]);

    $job = new DownloadMurekaSong($murekaCreation);
    $job->handle();

    Storage::disk('public')->assertExists("songs/{$song->id}");
    Storage::disk('public')->assertMissing("songs/{$song->id}/song.mp3");
});

it('handles HTTP timeout during download', function () {
    $song = Song::factory()->create();
    $murekaCreation = MurekaCreation::factory()->create([
        'song_id' => $song->id,
        'mureka_id' => 'song-123',
        'status' => 'succeeded',
        'choices' => [
            [
                'index' => 0,
                'url' => 'https://example.com/song.mp3',
                'flac_url' => 'https://example.com/song.flac',
                'duration' => 120
            ]
        ]
    ]);

    Http::fake([
        'https://example.com/song.mp3' => Http::timeout(1)->response('Timeout', 408)
    ]);

    $job = new DownloadMurekaSong($murekaCreation);
    $job->handle();

    Storage::disk('public')->assertMissing("songs/{$song->id}/song.mp3");
});

it('logs error when no downloadable songs are found', function () {
    $song = Song::factory()->create();
    $murekaCreation = MurekaCreation::factory()->create([
        'song_id' => $song->id,
        'mureka_id' => 'song-123',
        'status' => 'succeeded',
        'choices' => []
    ]);

    Http::fake();

    $job = new DownloadMurekaSong($murekaCreation);
    $job->handle();

    // No files should be created
    Storage::disk('public')->assertMissing("songs/{$song->id}");
    Http::assertNothingSent();
});

it('filters out choices without urls', function () {
    $song = Song::factory()->create();
    $murekaCreation = MurekaCreation::factory()->create([
        'song_id' => $song->id,
        'mureka_id' => 'song-123',
        'status' => 'succeeded',
        'choices' => [
            [
                'index' => 0,
                // Missing url
                'flac_url' => 'https://example.com/song1.flac',
                'duration' => 120
            ],
            [
                'index' => 1,
                'url' => 'https://example.com/song2.mp3',
                'flac_url' => 'https://example.com/song2.flac',
                'duration' => 130
            ]
        ]
    ]);

    Http::fake([
        'https://example.com/song2.mp3' => Http::response('mp3 content', 200)
    ]);

    $job = new DownloadMurekaSong($murekaCreation);
    $job->handle();

    // Only song2.mp3 should be downloaded
    Storage::disk('public')->assertExists("songs/{$song->id}/song2.mp3");
    Storage::disk('public')->assertMissing("songs/{$song->id}/song1.mp3");
    Storage::disk('public')->assertMissing("songs/{$song->id}/song1.flac");
});
