<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OllamaTools\SongCreator;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Tool;
use Prism\Prism\Prism;

class ToolController
{
    use ValidatesRequests;

    public function __invoke(Request $request)
    {
        $input = $this->validate($request, [
            'prompt' => ['required', 'string', 'max:255'],
        ]);

        $searchTool = Tool::as('search')
                          ->for('Search for user')
                          ->withStringParameter('name', 'The name of the person you are searching for with this tool')
                          ->using(function (string $name): string {

                              $user = User::where('name', 'like', "%{$name}%")->first();

                              if ($user) {
                                  return 'Found user: ' . $user->name . ' with email: ' . $user->email;
                              }

                              return 'User not found';
                          });

        #$songCreator = new SongCreator();

        $response = Prism::text()
                         ->using(Provider::Ollama, 'qwen3:4b')
                         ->withClientOptions(['timeout' => 60])
                         ->withPrompt($input['prompt'])
                         ->withTools([$searchTool])
                         ->withToolChoice('search')
                         ->withMaxSteps(2)
                         ->asText();

        return $response->text;
    }
}
