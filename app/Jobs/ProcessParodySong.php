<?php

namespace App\Jobs;

use App\Events\ParodySongCompleted;
use App\Models\Song;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ProcessParodySong implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $prompt,
        public ?int $userId = null
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $exitCode = Artisan::call('ollama:rhymes', [
                'prompt' => $this->prompt,
                '--show-prompt' => 0,
            ]);

            if ($exitCode === 0) {
                $song = Song::latest()->first();
                
                if ($song) {
                    broadcast(new ParodySongCompleted($song, $this->userId));
                    
                    Log::info('Parody song created successfully', [
                        'song_id' => $song->id,
                        'user_id' => $this->userId,
                    ]);
                }
            } else {
                Log::error('Failed to create parody song', [
                    'prompt' => $this->prompt,
                    'exit_code' => $exitCode,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception while creating parody song', [
                'message' => $e->getMessage(),
                'prompt' => $this->prompt,
            ]);
            
            throw $e;
        }
    }
}
