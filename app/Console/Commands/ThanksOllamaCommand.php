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

        $promptEmbeddingResponse = Prism::embeddings()
                                        ->using(Provider::Ollama, 'mxbai-embed-large')
                                        ->fromInput($userPrompt)
                                        ->asEmbeddings();

        // select orignal_text from document order by embedding <=> embedding
        $embeddingArray = $promptEmbeddingResponse->embeddings[0]->embedding;
        $formattedEmbedding = '[' . implode(',', $embeddingArray) . ']';

        $documents = Document::query()->select('original_text')
                             ->orderByRaw('embedding <=> ?::vector', [$formattedEmbedding])
                             ->limit(5)
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
        Each song created should have a verse and a chorus.

        Example Response:
        User: I want to write a song about a cat that loves to dance.
        Assistant: Sure! Here are some lyrics to get you started:

        Verse: "I’m a dancing cat, with a top hat and a cane,
        Twirling and spinning, I’m the king of the lane.

        Chorus: "Meow, meow, let’s dance all night,
        With my furry friends, we’ll groove till the light.


        Here is the user prompt:
        {$userPrompt}

        Here are some lyrics from the source collection that you can use as inspiration. There are multiple songs in the collection.:
        {$lyrics}

PROMPT;

    }
}
