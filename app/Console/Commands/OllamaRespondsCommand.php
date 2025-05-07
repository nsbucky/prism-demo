<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Symfony\Component\Console\Terminal;

class OllamaRespondsCommand extends Command
{
    protected $signature = 'ollama:responds {prompt? : The prompt to send to Ollama}';

    protected $description = 'Let\'s get the Ollama\'s attention';

    private string $responseText;

    public function handle()
    {
        $this->newLine();
        $this->components->info('🦙 Ollama Response Generator');
        $this->newLine();

        // https://www.youtube.com/watch?v=BYadMp8hwxQ
        $firePrompt = $this->argument('prompt') ?? 'Where is Uncle Nutzy\'s Clubhouse?';

        // Display the prompt
        $this->components->twoColumnDetail('Prompt', $firePrompt);
        $this->newLine();

        // Generate response
        $this->components->task('Generating response', function() use($firePrompt) {
            $response = Prism::text()
                             ->using(Provider::Ollama, 'llama3.2')
                             ->withClientOptions(['timeout' => 60])
                             ->withPrompt($firePrompt);

            $this->responseText = $response->asText()->text;
            return true;
        });

        $this->components->info('Response from Ollama (llama3.2 model):');
        $this->newLine();

        // Format the response in a box
        $width = min(100, $this->getTerminalWidth());
        $this->output->write('<fg=blue>┌' . str_repeat('─', $width - 2) . '┐</>' . PHP_EOL);

        foreach (explode("\n", wordwrap($this->responseText, $width - 4)) as $line) {
            $this->output->write('<fg=blue>│</> ' . Str::padRight($line, $width - 4) . ' <fg=blue>│</>' . PHP_EOL);
        }

        $this->output->write('<fg=blue>└' . str_repeat('─', $width - 2) . '┘</>' . PHP_EOL);

        $this->newLine();
        $this->components->info('Response complete!');

        return self::SUCCESS;
    }

    /**
     * Get the terminal width
     *
     * @return int
     */
    private function getTerminalWidth(): int
    {
        return (new Terminal())->getWidth();
    }
}
