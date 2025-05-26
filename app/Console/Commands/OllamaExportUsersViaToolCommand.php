<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Tool;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;

class OllamaExportUsersViaToolCommand extends Command
{
    protected $signature = 'ollama:tool-users {prompt?}';

    /*
     * This body holding me reminds me of my own mortality. Embrace this moment, remember, we are eternal, all this pain is an illusion
     */
    protected $description = 'Example of how to call custom tools with Ollama';

    public function handle()
    {
        // export users from Jan 1, 2025 to today, May 12, 2025
        $prompt = $this->argument('prompt') ?? 'Can you list all users created between '.now()->startOfMonth()->format('Y-m-d').' and '.now()->format('Y-m-d').'?';

        $searchTool = Tool::as('export_users')
            ->for('Export users according to the given criteria')
            ->withStringParameter('date_start', 'the start date of the period you want to search for')
            ->withStringParameter('date_end', 'the end date of the period you want to search for')
            ->using(function (string $date_start, string $date_end): string {

                try {
                    $dateStart = Carbon::parse($date_start);
                    $dateEnd = Carbon::parse($date_end);
                } catch (\Exception $e) {
                    return 'Date could not be figured out from input, sorry!';
                }

                $this->line("searching for users between {$dateStart->toDateTimeString()} and {$dateEnd->toDateTimeString()}");

                $users = User::whereBetween('created_at', [
                    $dateStart->toDateTimeString(),
                    $dateEnd->toDateTimeString(),
                ])->get();

                if ($users->isEmpty()) {
                    return 'Users not found';
                }

                $fp = fopen('php://temp', 'r+');

                fputcsv($fp, ['Name', 'Email', 'Created At']);

                foreach ($users as $user) {
                    fputcsv($fp, [
                        $user->name,
                        $user->email,
                        $user->created_at,
                    ]);
                }

                rewind($fp);

                return stream_get_contents($fp);
            });

        $response = Prism::text()
                         // ->using(Provider::Ollama, 'qwen3:4b')
            ->using(Provider::Ollama, 'llama3.2')
            ->withClientOptions(['timeout' => 60])
            ->withPrompt($prompt)
            ->withTools([$searchTool])
            ->withToolChoice('export_users')
            // *You should use a higher number of max steps if you expect your initial prompt to make multiple tool calls.
            ->withMaxSteps(2)
            ->asText();

        $this->components->info('LLM Response');

        $this->line($response->text);

        $this->newLine();
        $this->components->info('🦙 Ollama User Tool Results:');
        $this->newLine();

        if ($response->toolResults) {
            foreach ($response->toolResults as $toolResult) {
                echo 'Tool: '.$toolResult->toolName."\n";
                echo 'Result: '.$toolResult->result."\n";
            }
        }

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
