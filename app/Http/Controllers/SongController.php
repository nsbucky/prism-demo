<?php

namespace App\Http\Controllers;

use App\Console\Commands\OllamaRhymesWeirdlyCommand;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

class SongController
{
    use ValidatesRequests;

    public function __invoke(Request $request)
    {
        $input = $this->validate($request, [
            'prompt' => ['required', 'string', 'max:255'],
        ]);

        $buffer = new BufferedOutput;

        ob_start();

        Artisan::call(OllamaRhymesWeirdlyCommand::class, [
            'prompt' => $input['prompt'],
            '--show-prompt' => false,
        ], $buffer);

        return ob_get_clean();
    }
}
