<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Models\Song;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class CreateSongWithMureka implements ShouldQueue, ShouldBeUnique
{
    use Queueable, SerializesModels;

    protected const ENDPOINT = 'https://api.mureka.io/v1/song/generate';

    public function __construct(public Song $song)
    {
    }

    public function handle(): void
    {
        $response = Http::timeout(20)
                        ->asJson()
                        ->acceptJson()
                        ->withToken(config('services.mureka.api_key'))
                        ->post(self::ENDPOINT, [
                            'lyrics' => $this->song->lyrics,
                            'model'  => 'auto',
                            'prompt' => 'male vocal, polka, upbeat, happy'
                        ]);

        $this->handleResponse($response);
    }

    private function handleResponse(PromiseInterface|Response $response) :void
    {
        /*
        * response sample
        * {"id": "string","created_at": 0,"finished_at": 0,"model": "string","status": "string","failed_reason": "string","choices": [{"index": 0,"url": "string","flac_url": "string","duration": 0,"lyrics_sections": [{"section_type": "string","start": 0,"end": 0,"lines": [{"start": 0,"end": 0,"text": "string"}]}]}]}
        */

        /*
         * status types
         * status
            string

            The current status of the task
            Valid values
            preparing
            queued
            running
            succeeded
            failed
            timeouted
            cancelled
         */

        if ($response->failed()) {
            logger()->error('Song creation failed', $response->json());
            $this->delete();

            return;
        }

        $status = $response->json()['status'];

        if (in_array($status, ['failed', 'timeouted', 'canceled'])) {
            logger()->error('Song creation failed', $response->json());

            $this->delete();

            return;
        }

        if (in_array($status, ['preparing', 'running', 'queued'])) {

            $murekaCreation = $this->song->murekaCreations()->create([
                'mureka_id' => $response->json()['id'],
                'model'     => $response->json()['model'],
                'status'    => $status,
            ]);

            // launch another job to check on status
            CheckMurekaSongStatus::dispatch($murekaCreation)->delay(now()->addMinute());
        }
    }
}
