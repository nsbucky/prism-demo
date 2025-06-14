<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessParodySong;
use Illuminate\Http\Request;

class ParodySongController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'prompt' => 'required|string|min:5|max:500',
        ]);

        // Dispatch the job to process the parody song creation
        ProcessParodySong::dispatch(
            $validated['prompt'],
            $request->user()?->id
        );

        return back()->with('success', 'Your parody song is being created!');
    }
}
