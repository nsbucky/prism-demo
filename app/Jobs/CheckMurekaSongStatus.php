<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\MurekaCreation;
use App\Models\Song;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class CheckMurekaSongStatus implements ShouldQueue
{
    use Queueable;

    protected const ENDPOINT = 'https://api.mureka.ai/v1/song/query/';

    public function __construct(public MurekaCreation $murekaCreation) {}

    public function handle(): void
    {
        $response = Http::timeout(20)
            ->asJson()
            ->acceptJson()
            ->withToken(config('services.mureka.api_key'))
            ->get(self::ENDPOINT.$this->murekaCreation->mureka_id);

        $this->handleResponse($response);
    }

    private function handleResponse(PromiseInterface|Response $response): void
    {
        $status = $response->json('status');

        if (blank($status)
            || $response->failed()
            || in_array($status, ['failed', 'timeouted', 'canceled'])) {
            logger()->error('failed to check status on song', $response->json());

            $this->delete();

            return;
        }

        if (in_array($status, ['preparing', 'running', 'queued'])) {
            CheckMurekaSongStatus::dispatch($this->murekaCreation)
                ->delay(now()->addMinute());

            logger()->info('song is still processing', [
                'mureka_id' => $this->murekaCreation->mureka_id,
                'status' => $status,
            ]);

            return;
        }

        if ($status === 'succeeded') {
            $this->murekaCreation->update([
                'status' => $status,
                'choices' => $response->json()['choices'],
                'finished_at' => now(),
            ]);

            // dispatch a job to download the song
            DownloadMurekaSong::dispatch($this->murekaCreation);

            logger()->info('song created successfully', $response->json());
        }
    }
}
