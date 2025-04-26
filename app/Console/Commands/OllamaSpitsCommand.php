<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Prism\Prism\Prism; // omg why so many Prism classes
use Prism\Prism\Enums\Provider;

class OllamaSpitsCommand extends Command
{
    protected $signature = 'ollama:spits {feugo}';

    protected $description = 'Let\'s get the Ollama\'s attention';

    public function handle()
    {
        $validator = Validator::make(['feugo' => $this->argument('feugo')], rules: [
            'feugo' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        $input = $validator->validated();

        $response = Prism::text()
            ->using(Provider::Ollama, 'llama2')
            ->withClientOptions(['timeout' => 60])
            ->withPrompt($input['feugo']);

        $this->info($response->asText()->text);

        return self::SUCCESS;
    }
}
