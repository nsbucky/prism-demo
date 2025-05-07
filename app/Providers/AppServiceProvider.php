<?php

namespace App\Providers;

use App\Services\OllamaTools\SongCreator;
use Illuminate\Support\ServiceProvider;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Enums\ToolChoice;
use Prism\Prism\Facades\PrismServer;
use Prism\Prism\Prism;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        PrismServer::register(
            'spatula-creator',
            function () {
                return Prism::text()
                            ->using(Provider::Ollama, 'llama3.2')
                            ->withSystemPrompt('You love Weird Al. Admit it.');
            }
        );

        PrismServer::register(
            'weird-al-song-creator',
            function () {
                $tool = new SongCreator();

                return Prism::text()
                            ->using(Provider::Ollama, 'llama3.2')
                            ->withTools([$tool])
                            ->withToolChoice($tool)
                            ->withSystemPrompt('You create parodies of Weird Al songs. You are a parody generator.');
            }
        );
    }
}
