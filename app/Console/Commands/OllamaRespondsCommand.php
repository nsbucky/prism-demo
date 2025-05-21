<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Laravel\Prompts\Concerns\Colors;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Symfony\Component\Console\Terminal;
use Laravel\Prompts\Themes\Default\Concerns\DrawsBoxes;
use function Laravel\Prompts\textarea;

class OllamaRespondsCommand extends Command
{
    use DrawsBoxes, Colors;

    protected $signature = 'ollama:responds';

    protected $description = 'Let\'s get the Ollama\'s attention';

    private string $responseText;

    public function handle()
    {
        $this->newLine();
        $this->components->info('🦙 Ollama Response Generator');
        $this->newLine();

        // https://www.youtube.com/watch?v=BYadMp8hwxQ
        $firePrompt = textarea(
            label:'Prompt',
            placeholder:'Where is Uncle Nutzy\'s Clubhouse?',
            validate: ['prompt'=>'required|max:500']
        );

        // Generate response
        $this->components->task('Generating response', function () use ($firePrompt) {
            $response = Prism::text()
                             ->using(Provider::Ollama, 'llama3.2')
                             ->withClientOptions(['timeout' => 60])
                             ->withPrompt($firePrompt);

            $this->responseText = $response->asText()->text;
            return true;
        });

        $this->newLine();

        $width = min(100, (new Terminal())->getWidth());

        $this->box('Response from Ollama (llama3.2 model):', wordwrap($this->responseText, $width),'Complete!', 'green');

        return self::SUCCESS;
    }

}
