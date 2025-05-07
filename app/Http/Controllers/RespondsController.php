<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class RespondsController
{
    use ValidatesRequests;

    public function __invoke(Request $request)
    {
        $input = $this->validate($request, [
            'prompt' => ['required', 'string', 'max:255'],
        ]);

        $response = Prism::text()
                         ->using(Provider::Ollama, 'llama3.2')
                         ->withClientOptions(['timeout' => 60])
                         ->withSystemPrompt('Please limit your response to one sentence')
                         ->withPrompt($input['prompt']);

        return $response->asText()->text;
    }
}
