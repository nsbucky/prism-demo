<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Laravel\Prompts\Themes\Default\Concerns\DrawsBoxes;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Tool;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;

class OllamaListensToToolCommand extends Command
{
    use DrawsBoxes;

    protected $signature = 'ollama:tool {prompt?}';

    /*
     * This body holding me reinds me of my own mortality. Embrace this moment, remember, we are eternal, all this pain is an illusion
     */
    protected $description = 'Example of how to call custom tools with Ollama';

    public function handle()
    {
        $prompt = $this->argument('prompt') ?? 'Can you search for a user named Test User?';

        $searchTool = Tool::as('search')
                          ->for('Search for user')
                          ->withStringParameter('name', 'The name of the person you are stalking')
                          ->using(function (string $name): string {

                              $user = User::where('name', 'like', "%{$name}%")->first();

                              if ($user) {
                                  return 'Found user: ' . $user->name . ' with email: ' . $user->email;
                              }

                              return 'User not found';
                          });

        $response = Prism::text()
                         ->using(Provider::Ollama, 'qwen3:4b')
                         ->withClientOptions(['timeout' => 60])
                         //->withPrompt( $prompt .' /no_think')
                         ->withPrompt( $prompt .' /no_think')
                         ->withTools([$searchTool])
                         ->withToolChoice('search')
                         //*You should use a higher number of max steps if you expect your initial prompt to make multiple tool calls.
                         ->withMaxSteps(2)
                         ->asText();


        // replace the <think> tags from qwen silly robot
        $text = preg_replace('!<think>.*?</think>!s','',$response->text);
        $this->box('Ollama Response',$text);

        /*$this->newLine();
        $this->components->info('🦙 Ollama User Tool Results:');
        $this->newLine();

        if ($response->toolResults) {
            foreach ($response->toolResults as $toolResult) {
                echo "Tool: " . $toolResult->toolName . "\n";
                echo "Result: " . $toolResult->result . "\n";
            }
        }*/

        $this->newLine();
        $this->components->info('🦙 Ollama Assistant Messages:');
        $this->newLine();

        foreach ($response->responseMessages as $message) {
            if ($message instanceof AssistantMessage) {
                echo $message->content;
            }
        }

        $this->newLine();

        return self::SUCCESS;

    }
}
