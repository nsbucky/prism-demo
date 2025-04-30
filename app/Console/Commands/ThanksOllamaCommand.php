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

    protected $description = 'Predefined RAG prompt for song lyrics suggestions. Then have it read out as obama?';

    public function handle()
    {
        $prompts = [
            "Can you help me complete some song lyrics?
        I want to write a song in the style of Weird Al Yankovic.
        Here are the lyrics I have so far: 'I’m a loser baby, so why don't you kill me?'",

            "I bit a giraffe on the neck, and I made it cry.",

            "I smell like tuna, is it because I'm so fat?",

            "Elon Musk going to Mars to sell Mars bars",

            "My girlfriend is a robot, and she doesn't like me",

            'Tomatoes are banned in france, but I still eat them',

            'I like to ride the bus, but it makes me feel sick',

        ];

        $userPrompt = $prompts[6];

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
            return sprintf("Song ID:%s\nSong Title: %s\nLyrics: %s", $document->id, $document->name, $document->original_text);
        })->implode("\n\n");

        return <<<PROMPT
You are a songwriting assistant that helps users create parody songs similar to those of Weird Al Yankovic.
You have access to a collection of song lyrics that you can use as inspiration to provide the user some starting lyrics.
Songs should not be filthy or ambiguous. Refrain from using any profanity or suggestive lyrics.
The more silly and absurd the lyrics are the better. You are not supposed to write the entire song, just a title, verse, and chorus.
If there are no matches for source lyrics, try to use the lyrics provided by the user as a starting point.

*Import Notes*
You must provide a title, verse, chorus, and reason why you came up with these lyrics. The reason must reference the Weird Al song supplied in the source lyrics.

The song lyrics should match the syllable count for each line of the example song that is provided under the Source Lyrics heading.

The created song should match the format of the example song. The ending song should be a parody of the original song.

A musician should hopefully be able to sing the song because of the syllable count.

Example Song Output:
Reason: <reason for the song>
Title: <title of the song>
Verse: <verse of the song>
Chorus: <chorus of the song>

-------------

My user needs help coming up with a song. This is what they asked:
{$userPrompt}

Source Lyrics:
Here is a sample songs from the source collection that you can use as inspiration to guide the theme of the song you are creating for the user.

{$lyrics}

PROMPT;

    }
}
