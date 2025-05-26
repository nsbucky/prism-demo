<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\Themes\Default\Concerns\DrawsBoxes;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Symfony\Component\Console\Terminal;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;

class OllamaRespondsCommand extends Command
{
    use DrawsBoxes, Colors;

    protected $signature = 'ollama:responds';

    protected $description = 'Chat with Ollama in a continuous conversation';

    private array $messages = [];
    private int $terminalWidth;

    public function handle()
    {
        $this->terminalWidth = min(100, (new Terminal())->getWidth());

        $this->newLine();
        $this->components->info('🦙 Ollama Chat Interface');
        $this->components->info('Type "exit" or "quit" to end the conversation');
        $this->newLine();

        $defaultSillyMessage = "Weird Al says I should 'Burn your candle at both ends, Look a gift horse in the mouth, Mashed potatoes can be your friends'";

        // Start the chat loop
        while (true) {
            // Get user input
            $userMessage = text(
                label: 'You',
                placeholder: 'Type your message...',
                default: $defaultSillyMessage,
                required: true,
            );

            // reset the default message
            $defaultSillyMessage = '';

            // Check for exit commands
            if (in_array(strtolower($userMessage), ['exit', 'quit', 'bye', 'goodbye'])) {
                $this->newLine();
                $this->components->info('👋 Thanks for chatting! Goodbye!');
                break;
            }

            // Add user message to conversation history
            $this->messages[] = new UserMessage($userMessage);

            // Generate response
            $this->newLine();
            $this->components->task('Ollama is thinking...', function () {
                $response = Prism::text()
                                 ->using(Provider::Ollama, 'llama3.2')
                                 ->usingTemperature(0.7)
                                 ->withClientOptions(['timeout' => 60])
                                 ->withMessages($this->messages);

                // Add assistant response to conversation history
                $this->messages[] = new AssistantMessage($response->asText()->text);

                return true;
            });

            // Display the response
            $this->newLine();
            $this->displayAssistantResponse();
            $this->newLine();

            // Optional: Ask if user wants to continue
            if (count($this->messages) > 10 && !confirm('Continue chatting?')) {
                $this->newLine();
                $this->components->info('👋 Thanks for the conversation!');
                break;
            }
        }

        return self::SUCCESS;
    }

    private function displayAssistantResponse(): void
    {
        $lastMessage = end($this->messages);

        if ($lastMessage instanceof AssistantMessage) {
            $wrappedText = wordwrap($lastMessage->content, $this->terminalWidth - 4);

            $this->box('🦙 Ollama', $wrappedText, '', 'cyan');
        }
    }

}
