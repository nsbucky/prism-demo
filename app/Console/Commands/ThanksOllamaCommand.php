<?php

namespace App\Console\Commands;

use App\Models\Document;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class ThanksOllamaCommand extends Command
{
    protected $signature = 'ollama:thanks';

    protected $description = 'Predefined RAG prompt for song lyrics suggestions';

    public function handle()
    {
        $userPrompt = "Can you help me complete some song lyrics?
        I want to write a song in the style of Weird Al Yankovic.
        Here are the lyrics I have so far: 'I’m a loser baby, so why don't you kill me?'";

        $userPrompt = "Can you help me complete some song lyrics?
        I want to write a song in the style of Weird Al Yankovic.
        Here are the lyrics I have so far: I smell like tuna, is it because I'm so fat?";


        $userPrompt = "Can you help me complete some song lyrics?
        I want to write a song in the style of Weird Al Yankovic.
        Here are the lyrical ideas I have so far: Elon Musk going to Mars to sell Mars bars";

        $promptEmbeddingResponse = Prism::embeddings()
                                        ->using(Provider::Ollama, 'mxbai-embed-large')
                                        ->fromInput($userPrompt)
                                        ->asEmbeddings();

        // select orignal_text from document order by embedding <=> embedding
        $embeddingArray = $promptEmbeddingResponse->embeddings[0]->embedding;
        $formattedEmbedding = '[' . implode(',', $embeddingArray) . ']';

        $documents = Document::query()->select('original_text')
                             ->orderByRaw('embedding <=> ?::vector', [$formattedEmbedding])
                             ->limit(1)
                             ->get();

        $finalPrompt = $this->buildPrompt($userPrompt, $documents);

        $this->info('Pre-formatted prompt with guardrails:');

        $this->info($finalPrompt);

        $response = Prism::text()
                         ->using(Provider::Ollama, 'llama3.2')
                         ->withClientOptions(['timeout' => 60])
                         ->withPrompt($finalPrompt)
                         ->asText();

        $this->line('Response:');
        $this->line($response->text);
    }

    private function buildPrompt(string $userPrompt, Collection $documents): string
    {
        $lyrics = $documents->map(function ($document) {
            return sprintf("Title: %s\nLyrics: %s", $document->name, $document->original_text);
        })->implode("\n\n");

        return <<<PROMPT
You are a song writing assistant that helps users create parody songs similar to those of Weird Al Yankovic.
You have access to a collection of song lyrics that you can use as inspiration to provide the user some starting lyrics.
The user will provide you with a prompt and you will use the lyrics from the source lyrics collection to help them complete their song.
Songs should not be filthy or ambiguous. Refrain from using any profanity or suggestive lyrics. The more silly and absurd the lyrics, the better.
Each song created should have a verse and a chorus. The song lyrics should match the syllable count for each line of the example song.
The created song ideally should not contain any lyrical ideas that are similar to the source lyrics. The ending song should be a parody of the original song. A musician should be able to sing the song.

Example Song (Your response):
Title: <title of the song>
Verse: <verse of the song>
Chorus: <chorus of the song>

User Prompt:
{$userPrompt}

Source Lyrics:
Here is a sample songs from the source collection that you can use as inspiration ot guide the theme of the song you are creating for the user.
These lyrics are by Weird Al Yankovic and are in the style of parody songs. This song should help you, the assistant, to create a new song.:

{$lyrics}

PROMPT;

    }
}
