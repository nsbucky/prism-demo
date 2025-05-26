<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\Themes\Default\Concerns\DrawsBoxes;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Tool;
use Prism\Prism\Prism;

use function Laravel\Prompts\table;
use function Laravel\Prompts\text;

class OllamaListensToToolCommand extends Command
{
    use Colors, DrawsBoxes;

    protected $signature = 'ollama:tool';

    protected $description = 'Example of how to call custom tools with Ollama';

    private $response;

    public function handle()
    {
        $this->newLine();
        $this->components->info('🦙 Ollama Tool Usage Example');
        $this->newLine();

        // Get prompt from user input
        $prompt = text(
            label: 'What would you like to search for?',
            placeholder: 'e.g., Can you search for a user named Weird Al? How many users are there?',
            default: 'Can you search for a user named Ollama?',
            required: true
        );

        // Define the search tools
        $searchTool = Tool::as('search')
            ->for('Search for user')
            ->withStringParameter('name', 'The name of the person you are searching for')
            ->using(function (string $name): string {
                $user = User::where('name', 'like', "%{$name}%")->first();

                if ($user) {
                    return 'Found user: '.$user->name.' with email: '.$user->email;
                }

                return 'User not found';
            });

        $countTool = Tool::as('count')
            ->for('Count the number of users')
            ->using(function (): string {
                $userCount = User::count();

                return "Total number of users: {$userCount}";
            });

        $this->newLine();
        $this->components->task('Ollama is searching...', function () use ($prompt, $searchTool, $countTool) {
            $this->response = Prism::text()
                ->using(Provider::Ollama, 'qwen3:4b')
                ->withClientOptions(['timeout' => 60])
                ->withPrompt($prompt.' /no_think')
                ->withTools([$searchTool, $countTool])
                                   // ->withToolChoice('search')
                ->withMaxSteps(2)
                ->asText();

            return true;
        });

        // Display the response
        $this->newLine();
        $this->box('🦙 Ollama Response', $this->response->text, '', 'cyan');

        // Display tool results in a table if available
        if ($this->response->toolResults && count($this->response->toolResults) > 0) {
            $this->newLine();
            $this->components->info('🔧 Tool Execution Results:');
            $this->newLine();

            // Prepare data for table
            $tableData = collect($this->response->toolResults)->map(function ($toolResult) {
                return [
                    'Tool Name' => $toolResult->toolName,
                    'Result' => wordwrap($toolResult->result, 60),
                ];
            })->toArray();

            // Display as table
            table(
                headers: ['Tool Name', 'Result'],
                rows: $tableData
            );
        }

        $this->newLine();

        return self::SUCCESS;
    }
}
