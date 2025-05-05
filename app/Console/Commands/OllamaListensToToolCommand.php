<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Tool;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;

class OllamaListensToToolCommand extends Command
{
    protected $signature = 'ollama:tool {name}';

    /*
     * This body holding me reminds me of my own mortality. Embrace this moment, remember, we are eternal, all this pain is an illusion
     */
    protected $description = 'Example of how to call custom tools with Ollama';

    public function handle()
    {
        $validator = Validator::make(
            data: [
                'name' => $this->argument('name')],
            rules: [
                'name' => ['required', 'string', 'max:255'],
            ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        $input = $validator->validated();

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

        $this->info('Searching for ' . $input['name']);

        $response = Prism::text()
                         ->using(Provider::Ollama, 'llama3.2')
                         ->withClientOptions(['timeout' => 60])
                         ->withPrompt('Can you find this user? I am searching for them: ' . $input['name'])
                         ->withTools([$searchTool])
                         ->withMaxSteps(2) // so that it uses the tool
                         ->asText();

        $this->line($response->text);

        if ($response->toolResults) {
            foreach ($response->toolResults as $toolResult) {
                echo "Tool: " . $toolResult->toolName . "\n";
                echo "Result: " . $toolResult->result . "\n";
            }
        }

        /*foreach ($response->responseMessages as $message) {
            if ($message instanceof AssistantMessage) {
                echo $message->content;
            }
        }*/

        return self::SUCCESS;

    }
}
