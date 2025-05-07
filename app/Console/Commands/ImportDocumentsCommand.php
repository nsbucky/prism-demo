<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Lyric;
use Illuminate\Console\Command;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class ImportDocumentsCommand extends Command
{
    protected $signature = 'import-documents';

    protected $description = 'Import documents from a specified source';

    public function handle()
    {
        $importDirectory = config('services.import_directory');

        if (!is_dir($importDirectory)) {
            $this->error('Import directory does not exist: ' . $importDirectory);
            return self::FAILURE;
        }

        $files = glob($importDirectory . '/lyrics/*.txt');

        $this->info('Importing documents from: ' . $importDirectory);

        $this->info('Found ' . count($files) . ' files to import.');

        foreach ($files as $file) {
            $this->info('Importing file: ' . $file);
            $content = file_get_contents($file);

            if (blank(trim($content))) {
                $this->error('File is empty or contains only whitespace: ' . $file);
                continue;
            }

            $response = Prism::embeddings()
                             ->withClientOptions(['timeout' => 60])
                             ->using(Provider::Ollama, 'mxbai-embed-large')
                             ->fromInput($content)
                             ->asEmbeddings();

            Lyric::create([
                'name'          => basename($file, '.html'),
                'embedding'     => $response->embeddings[0]->embedding,
                'original_text' => $content
            ]);

        }

        return self::SUCCESS;
    }
}
