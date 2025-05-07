<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Lyric;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Tool;
use Prism\Prism\Tool as ToolBand;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;

class OllamaCantFindTheSongCommand extends Command
{
    protected $signature = 'ollama:lyrics {lyric}';

    protected $description = 'Example of how to call custom tools with Ollama';

    public function handle()
    {
        $validator = Validator::make(
            data: [
                'lyric' => $this->argument('lyric')],
            rules: [
                'lyric' => ['required', 'string', 'max:255'],
            ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        $input = $validator->validated();

        $this->info('Searching for ' . $input['lyric']);

        $response = Prism::text()
                         ->using(Provider::Ollama, 'qwen3:4b') // llama3.2
                         ->withClientOptions(['timeout' => 60])
                         ->withPrompt($this->getPrompt($input['lyric']))
                         ->withTools([$this->getSearchTool()])
                         ->withMaxSteps(2) // so that it uses the tool
                         ->asText();

        $this->line($response->text);


        if ($response->toolResults) {
            foreach ($response->toolResults as $toolResult) {
                echo "Tool: " . $toolResult->toolName . "\n";
                echo "Result: " . $toolResult->result . "\n";
            }
        } else {
            $this->error('No results found, maybe tool was not used?');
        }

        /*foreach ($response->responseMessages as $message) {
            if ($message instanceof AssistantMessage) {
                echo $message->content;
            }
        }*/

        return self::SUCCESS;

    }

    private function getSearchTool(): ToolBand
    {
        return Tool::as('lyric-search-tool')
                   ->for('Search for lyric in our database')
                   ->withStringParameter('lyric','Song lyric you are searching for')
                   ->using(function (string $lyric): string {

                       $lyric = Lyric::where('original_text', 'ilike', "%{$lyric}%")->first();

                       if ($lyric) {
                           $result = 'This song called '.$lyric->name.' contains lyrics that match. Please extract part of the match in the response '
                                  . Str::limit($lyric->original_text);

                           return $result;
                       }

                       return 'No song matches';
                   });
    }

    /**
     * @param $lyric
     * @return string
     */
    public function getPrompt($lyric): string
    {
        return sprintf('You must use the provided lyric-search-tool to find a song
        in our database with the lyric: %s. Do not use any other source to search. If no match is found then stop.', $lyric);
    }
}
