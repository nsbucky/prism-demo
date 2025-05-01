<?php

namespace App\Console\Commands;

use App\Models\Document;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class ThanksOllamaCommand extends Command
{
    protected $signature = 'thanks:ollama';

    protected $description = 'Predefined RAG prompt for song lyrics suggestions. Then have it read out as obama?';

    public function handle()
    {
        $prompts = [
            "I like to use the Ollama LLM",
            "Can you help me complete some song lyrics? I want to write a song in the style of Weird Al Yankovic.
            Here are the lyrics I have so far: 'I’m a loser baby, so why don't you kill me?'",

            "I bit a giraffe on the neck, and I made it cry.",

            "I smell like tuna, is it because I'm so fat?",

            "Elon Musk going to Mars to sell Mars bars",

            "My girlfriend is a robot, and she doesn't like me",

            'Tomatoes are banned in france, but I still eat them',

            'I like to ride the bus, but it makes me feel sick',
        ];

        $userPrompt = $prompts[array_rand($prompts)];

        $promptEmbeddingResponse = Prism::embeddings()
                                        ->using(Provider::Ollama, 'mxbai-embed-large')
                                        ->fromInput($userPrompt)
                                        ->asEmbeddings();

        // select orignal_text from document order by embedding <=> embedding
        $embeddingArray = $promptEmbeddingResponse->embeddings[0]->embedding;

        $formattedEmbedding = '[' . implode(',', $embeddingArray) . ']';

        $document = Document::query()->select('original_text')
                            ->orderByRaw('embedding <=> ?::vector', [$formattedEmbedding])
                            ->first();
        if (!$document) {
            $this->error('No document found for the given embedding.');
            return self::FAILURE;
        }

        $finalPrompt = $this->buildPrompt($userPrompt, $document);

        $this->info('Pre-formatted prompt with guardrails:');

        $this->line($finalPrompt);

        $response = Prism::text()
                         ->using(Provider::Ollama, 'llama3.2')
                         ->withClientOptions(['timeout' => 60])
                         ->withPrompt($finalPrompt)
                         ->asText();

        $this->line('Response:');
        $this->line($response->text);

        return self::SUCCESS;
    }

    private function buildPrompt(string $userPrompt, Document $document): string
    {
        return <<<PROMPT
You are a songwriting assistant that helps users create parody songs similar to those of Weird Al Yankovic.
The source lyrics are to be used as inspiration to create a new song.
The sample song you create only needs a title, verse, and chorus.
Songs should not be filthy or ambiguous. Refrain from using any profanity or suggestive lyrics.
Silly and absurd the lyrics are preferred in the output.

*Import Notes*
You must provide a title, verse, chorus, and reason why you came up with these lyrics. The reason must reference the Weird Al song supplied in the source lyrics.
The song lyrics should match the syllable count for each line of the example song that is provided under the Source Lyrics heading.
The created song should match the syllable count of the example song provided in the Source Lyrics section.
The song should be a parody of the original song.

Example Song Output:
Reason
<reason for using the source lyrics as inspiration>

Title
<title>

Verse
<verse>

Chorus
<chorus>

-------------

Please create a song using the following prompt and source lyrics:
{$userPrompt}

Source Lyrics
Here is a sample song from the database that is similar to the song you are trying to create.

Song ID (this is the ID of the song in the database)
{$document->id}

Song Title
{$document->name}

Lyrics
{$document->original_text}

PROMPT;

    }
}
