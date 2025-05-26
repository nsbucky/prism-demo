<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Models\MurekaCreation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DownloadMurekaSong implements ShouldQueue
{
    use Queueable;

    public function __construct(public MurekaCreation $murekaCreation)
    {
        //
    }

    public function handle(): void
    {
        $this->murekaCreation->downloadableSongs()->each(function ($song) {
            $this->downloadSong($song['url']);
        })->whenEmpty(function () {
            logger()->error('no downloadable songs found', [
                'mureka_id' => $this->murekaCreation->mureka_id,
                'choices'   => $this->murekaCreation->choices,
            ]);
        });
    }

    private function downloadSong($url): void
    {
        // does the file already exist?
        $basePath = 'songs/' . $this->murekaCreation->song_id;
        $filePath = $basePath . '/' . basename($url);

        if (Storage::disk('public')->exists($filePath)) {
            return;
        }

        // make sure the directory exists
        if (!Storage::disk('public')->exists($basePath)) {
            Storage::disk('public')->makeDirectory($basePath);
        }

        $response = Http::sink(Storage::disk('public')->path($filePath))
                        ->get($url);

        if ($response->failed()) {
            logger()->error('failed to download song', [
                'url'    => $url,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            // remove the file if download failed
            Storage::disk('public')->delete($filePath);

            return;
        }

        logger()->info('song downloaded successfully', [
            'url' => $url,
        ]);

    }
}
