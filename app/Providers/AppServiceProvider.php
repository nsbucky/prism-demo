<?php

namespace App\Providers;

use App\Models\Song;
use Illuminate\Support\ServiceProvider;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\PrismServer;
use Prism\Prism\Facades\Tool;
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
                            ->using(Provider::Ollama, 'qwen3:4b')
                            ->withSystemPrompt('You must turn every conversation into a conversation about spatulas.');
            }
        );

        PrismServer::register(
            'created-songs-search',
            function () {

                $searchSongsTool = Tool::as('search-songs')
                                       ->for('Searching Songs')
                                       ->withStringParameter('title', 'The title of the song')
                                       ->withStringParameter('lyric', 'The lyric of the song')
                                       ->withStringParameter('keywords', 'Keywords in the song')
                                       ->using(function (string $title = null, string $lyric = null, string $keywords = null) {
                                           if (blank($title) && blank($lyric) && blank($keywords)) {
                                               return 'No song found';
                                           }

                                           return Song::query()
                                                      ->when($title, function ($query, $title) {
                                                          return $query->where('title', 'like' . '%' . $title . '%');
                                                      })->when($lyric, function ($query, $lyric) {
                                                   return $query->where('lyrics', 'like' . '%' . $lyric . '%');
                                               })
                                                      ->when($keywords, function ($query, $keywords) {
                                                          return $query->where('keywords', 'like' . '%' . $keywords . '%');
                                                      })
                                                      ->limit(5)
                                                      ->toJson();
                                       });

                return Prism::text()
                            ->using(Provider::Ollama, 'qwen3:4b')
                            ->withTools([$searchSongsTool])
                            ->withMaxSteps(2)
                            ->withToolChoice('search-songs');
            }
        );
    }
}
