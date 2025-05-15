<?php

namespace App\Console\Commands;

use App\Models\Lyric;
use App\Models\Song;
use App\Services\OllamaTools\SongCreator;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Pipeline;
use Illuminate\Support\Str;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Symfony\Component\Console\Terminal;

class OllamaRhymesWeirdlyCommand extends Command
{
    protected $signature = 'ollama:rhymes
                            {prompt? : The sentence or two you would like use as a base for your song}
                            {--no-rag : run without RAG}
                            {--show-prompt=1: Omit prepared prompt from response}
                            ';

    protected $description = 'Semantic RAG prompt example for song lyrics.';

    private string $userPrompt;

    private $promptView;

    private Song $song;

    public function handle()
    {
        $userPrompt = $this->getUserPrompt();

        if (blank(trim($userPrompt))) {
            $this->error('Please provide a valid prompt.');

            return self::FAILURE;
        }

        $this->userPrompt = $userPrompt;

        $this->song = new Song([
            'prompt' => $this->userPrompt,
            'title'  => Str::limit(trim($this->argument('prompt')), 100),
        ]);

        $this->newLine();
        $this->components->info('🦙 Ollama Lyrical RAG');
        $this->newLine();

        $this->components->task('Building formatted prompt', function () {

            $this->promptView = view('lyrics', [
                'userPrompt' => $this->buildUserPrompt(),
                'keywords'   => $this->extractKeywords(),
                'document'   => $this->getMatchingLyric(),
            ]);

            $this->song->formatted_prompt = $this->promptView->render();

            return true;
        });

        if ($this->option('show-prompt')) {
            $this->newLine();
            $this->line($this->promptView);
            $this->newLine();
        }

        $this->components->task('Generating song lyrics', function () {

            $response = Prism::text()
                             ->using(Provider::Ollama, 'llama3.2')
                             ->withClientOptions(['timeout' => 120, 'usingTemperature' => 0.7])
                             ->withPrompt($this->promptView)
                             ->asText();

            $this->components->info('Song created by Ollama');

            $this->song->lyrics = $response->text;

            $this->formattedSong($response->text);

            return true;
        });

        $this->song->save();

        return self::SUCCESS;
    }

    private function formattedSong($text)
    {
        $width = min(100, (new Terminal())->getWidth());

        $this->output->write('<fg=blue>┌' . str_repeat('─', $width - 2) . '┐</>' . PHP_EOL);

        foreach (explode("\n", wordwrap($text, $width - 4)) as $line) {
            $this->output->write('<fg=blue>│</> ' . Str::padRight($line, $width - 4) . ' <fg=blue>│</>' . PHP_EOL);
        }

        $this->output->write('<fg=blue>└' . str_repeat('─', $width - 2) . '┘</>' . PHP_EOL);
    }

    private function usingRag(): bool
    {
        return !$this->option('no-rag');
    }

    private function extractKeywords(): string
    {
        if (!$this->usingRag()) {
            return '';
        }

        $response = Prism::text()
                         ->using(Provider::Ollama, 'llama3.2')
                         ->withClientOptions(['timeout' => 120])
                         ->withPrompt(view('keywords', ['userPrompt' => $this->userPrompt]))
                         ->asText();

        $keywords = (string)object_get($response, 'text', '');

        $this->line('Extracted keywords:');
        $this->line($keywords);

        $this->song->keywords = $keywords;

        return $keywords;
    }

    private function getMatchingLyric(): ?Lyric
    {
        if (!$this->usingRag()) {
            return null;
        }

        $promptEmbeddingResponse = Prism::embeddings()
                                        ->using(Provider::Ollama, 'mxbai-embed-large')
                                        ->withClientOptions(['timeout' => 120]) // Add client options as an associative array
                                        ->fromInput($this->userPrompt)
                                        ->asEmbeddings();

        // select original_text from document order by embedding <=> embedding
        $embeddingArray = $promptEmbeddingResponse->embeddings[0]->embedding;

        $formattedEmbedding = '[' . implode(',', $embeddingArray) . ']';

        /*$lyrics = Lyric::query()
                       ->select(['id', 'name', 'original_text'])
                       ->orderByRaw('embedding <=> ?::vector', [$formattedEmbedding])
                       ->limit(3)
                       ->get();

        $lyric = $this->askForLyric($lyrics);

        if ($lyric) {
            $this->song->matched_lyrics = $lyric->toArray();
        }

        return $lyric;*/


        $lyric = Lyric::query()
                       ->select(['id', 'name', 'original_text'])
                       ->orderByRaw('embedding <=> ?::vector', [$formattedEmbedding])
                       ->limit(1)
                       ->first();

        if ($lyric) {
            $this->song->matched_lyrics = $lyric->toArray();
        }

        return $lyric;
    }

    private function buildUserPrompt(): string
    {
        /*
         * Why Process Prompts Before Embedding?
         * Improved Semantic Similarity: Embedding models aim to capture the meaning of text. 1 Noise like stop words,
         * capitalization differences, and punctuation can sometimes obscure the core semantic content, leading to less
         * accurate similarity comparisons with your document embeddings.
         */

        /*
         * Lowercasing: Generally Recommended. Converting the entire prompt to lowercase ensures that the embedding
         * model doesn't treat "Search" and "search" as different concepts. This is a simple but often effective step.
         *
         * Stop Word Removal: Consider Carefully. Removing common words like "the," "a," "is," "and" can sometimes be
         * helpful by focusing the embedding on more content-rich terms. However, stop words can also contribute to the
         * overall meaning and context of a query. Removing them aggressively might lead to the loss of important nuances,
         * especially in shorter queries. It's often better to rely on the embedding model's ability to weigh the
         * importance of different words. You might experiment with and without stop word removal to see what works best
         * for your specific data and use case.
         *
         * Punctuation Removal: Generally Recommended. Punctuation marks usually don't contribute significantly to the
         * semantic meaning for retrieval purposes. Removing them can simplify the input for the embedding model.
         *
         * Stemming/Lemmatization: Consider Carefully. These techniques reduce words to their root form
         * (e.g., "running" to "run," "better" to "good"). While they can help in document embedding by grouping similar concepts,
         *  they might be less crucial for query embedding.
         *
         * User queries are often already in a relatively concise form. Over-aggressive stemming/lemmatization on
         * queries could even alter the intended meaning.
         *
         * Special Character Removal: Generally Recommended (with caution). Removing irrelevant special characters can
         * be beneficial. However, be careful not to remove characters that might be part of a specific search term
         * (e.g., hashtags, code snippets).
         */
        return Pipeline::send($this->userPrompt)
                       ->through([
                           // Lowercase
                           fn($input) => mb_strtolower($input),

                           // Remove stop words
                           fn($input) => preg_replace('/\b(?:the|a|is|and)\b/', '', $input),

                           // Remove punctuation
                           fn($input) => preg_replace('/[^\w\s]/u', '', $input),

                           // Remove special characters
                           fn($input) => preg_replace('/[^\p{L}\p{N}\s]/u', '', $input),
                       ])
                       ->then(fn($userPrompt) => $userPrompt);
    }

    /**
     * @return string
     */
    public function getUserPrompt(): string
    {
        if (filled($this->argument('prompt'))) {
            return (string)$this->argument('prompt');
        }

        $prompts = [
            // Original prompt
            "I like to use the Ollama LLM",

            // Song lyrics completion
            'I’m a loser baby, so why don\'t you kill me ?',

            // Giraffe encounter
            "I bit a giraffe on the neck, and I made it cry.",

            // Tuna-related joke
            "I smell like tuna, is it because I'm so fat ? ",

            // Elon Musk's Mars bar scheme
            "Elon Musk going to Mars to sell Mars bars",

            // Robot girlfriend drama
            "My girlfriend is a robot, and she doesn't like me",

            // Banned tomatoes in France
            'Tomatoes are banned in france, but I still eat them',

            // Bus-related anxiety
            'I like to ride the bus, but it makes me feel sick',

            // Pet rock takeover plans
            "I'm secretly training my pet rock to take over the world . ",

            // Silly Squares cereal jingle
            "Can you help me come up with a catchy jingle for a fictional cereal brand called 'Silly Squares' ? ",

            // Rainbow-colored chicken party
            "Why did the chicken wear a rainbow - colored tutu to the party ? ",

            // Exploding frozen pizza microwave mishap
            "I've been trying to cook a frozen pizza in the microwave, but it keeps exploding.",

            // Mars rollercoaster entrepreneur
            "Elon Musk just announced that he's building a rollercoaster on Mars to attract tourists . ",

            // Snail racing cat obsession
            "My cat thinks it's a professional snail trainer and has a PhD in slimy sports.",

            // Pineapple pizza presidency platform
            "I'm running for president on a platform of making pineapple pizza a national dish . ",

            // Time-traveling accountant struggles
            "Can you help me write a poem about the struggles of being a time - traveling accountant ? ",

            // Banana health issues
            "Why did the banana go to the doctor ? It wasn't peeling well.",

            // Cat video marathoning exhaustion
            "I've been trying to break the world record for most consecutive hours spent watching cat videos, but I think I need a nap . ",

            // Parrot Shakespearean actor
            "My friend's parrot can recite the entire script of 'Hamlet' in iambic pentameter.",

            // Moo-sical cow band
            "What do you call a group of cows playing instruments? A moo-sical band.",

            // Desk surfing mishap
            "I've been trying to learn how to surf on my desk, but I keep wiping out . ",

            // Scarecrow award winner
            "Why did the scarecrow win an award ? Because he was outstanding in his field . ",

            // Country cow rockstar aspirations
            "Can you help me come up with a song title and lyrics for a country song about a cow who wants to be a rockstar ? ",

            // Nacho cheese restaurant banishment
            "I just got kicked out of my favorite restaurant for eating too much nacho cheese . ",

            // Can't opener conundrum
            "What do you call a can opener that doesn't work? A can't opener . ",

            // Ninja dog sneakiness
            "My dog thinks it's a professional ninja and has been sneaking around the house at night.",

            // Astronaut relationship issues
            "Why did the astronaut break up with his girlfriend? Because he needed space.",

            // Goldfish trick-training struggles
            "I've been trying to train my goldfish to do tricks, but it just keeps swimming away . "
        ];


        return $prompts[array_rand($prompts)];
    }

    private function askForLyric(Collection $lyrics): ?Lyric
    {
        $response = Prism::text()
                         ->using(Provider::Ollama, 'llama3.2')
                         ->withClientOptions(['timeout' => 120])
                         ->withPrompt(view('matching-lyrics', [
                             'userPrompt' => $this->userPrompt,
                             'lyrics'     => $lyrics,
                         ]))
                         ->asText();

        $songId = $response->text;

        $this->components->twoColumnDetail('Selected Lyric', $songId);

        if (is_numeric($songId)) {
            return $lyrics->firstWhere('id', $songId);
        }

        return $lyrics->first();
    }

}
