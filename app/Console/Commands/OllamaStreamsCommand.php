<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class OllamaStreamsCommand extends Command
{
    protected $signature = 'ollama:streams {prompt}';

    protected $description = 'Stream the output to the terminal';

    public function handle()
    {
        $validator = Validator::make(['prompt' => $this->argument('prompt')], rules: [
            'prompt' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        $input = $validator->validated();

        $response = Prism::text()
                         ->using(Provider::Ollama, 'llama3.2')
                         ->withClientOptions(['timeout' => 60])
                         ->withPrompt($input['prompt'])
                         ->asStream();

        foreach ($response as $chunk) {
            $this->line($chunk->text);

            if($chunk->finishReason) {
                $this->info('Chunk finished');
            }

            flush();
        }

        return self::SUCCESS;
    }
}
