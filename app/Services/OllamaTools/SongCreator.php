<?php

namespace App\Services\OllamaTools;

use App\Models\Lyric;
use Illuminate\Support\Facades\Pipeline;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\Tool;

class SongCreator extends Tool
{
    public function __construct()
    {
        $this->as('weird-al-song-creator')
            ->for('when you want to create a parody of a Weird Al song')
            ->withStringParameter('theme', 'The theme you want to use for the parody')
            ->using($this);
    }

    public function __invoke(string $theme)
    {
        $normalizedPrompt = $this->buildUserPrompt($theme);

        $promptView = view('lyrics', [
            'userPrompt' => $normalizedPrompt,
            'document' => $this->getMatchingDocument($normalizedPrompt),
            'keywords' => $this->extractKeywords($normalizedPrompt),
        ]);

        $response = Prism::text()
            ->using(Provider::Ollama, 'llama3.2')
            ->withClientOptions(['timeout' => 60])
            ->withPrompt($promptView)
            ->asText();

        return $response->text;
    }

    private function extractKeywords($prompt): string
    {
        $response = Prism::text()
            ->using(Provider::Ollama, 'llama3.2')
            ->withClientOptions(['timeout' => 60])
            ->withPrompt(view('keywords', ['userPrompt' => $prompt]))
            ->asText();

        $keywords = (string) object_get($response, 'text', '');

        return $keywords;
    }

    private function getMatchingDocument($prompt): ?Lyric
    {
        $promptEmbeddingResponse = Prism::embeddings()
            ->using(Provider::Ollama, 'mxbai-embed-large')
            ->fromInput($prompt)
            ->asEmbeddings();

        // select original_text from document order by embedding <=> embedding
        $embeddingArray = $promptEmbeddingResponse->embeddings[0]->embedding;

        $formattedEmbedding = '['.implode(',', $embeddingArray).']';

        return Lyric::query()
            ->select(['id', 'name', 'original_text'])
            ->orderByRaw('embedding <=> ?::vector', [$formattedEmbedding])
            ->first();
    }

    private function buildUserPrompt($prompt): string
    {
        return Pipeline::send($prompt)
            ->through([
                // Lowercase
                function ($input) {
                    return mb_strtolower($input);
                },

                // Remove stop words
                function ($input) {
                    return preg_replace('/\b(?:the|a|is|and)\b/', '', $input);
                },

                // Remove punctuation
                function ($input) {
                    return preg_replace('/[^\w\s]/u', '', $input);
                },

                // Remove special characters
                function ($input) {
                    return preg_replace('/[^\p{L}\p{N}\s]/u', '', $input);
                },
            ])
            ->then(function ($userPrompt) {
                return $userPrompt;
            });
    }
}
