<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OllamaTools\SongCreator;
use Carbon\Carbon;
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

        $countTool = Tool::as('count')
                         ->for('Count the number of users')
                         ->using(function (): string {
                             $userCount = User::count();
                             return "Total number of users: {$userCount}";
                         });

        $dateSearchTool = Tool::as('export_users')
                          ->for('Export users according to the given criteria')
                          ->withStringParameter('date_start', 'the start date of the period you want to search for')
                          ->withStringParameter('date_end', 'the end date of the period you want to search for')
                          ->using(function (string $date_start, string $date_end): string {

                              try {
                                  $dateStart = Carbon::parse($date_start);
                                  $dateEnd   = Carbon::parse($date_end);
                              } catch (\Exception $e) {
                                  return 'Date could not be figured out from input, sorry!';
                              }

                              $users = User::whereBetween('created_at', [
                                  $dateStart->toDateTimeString(),
                                  $dateEnd->toDateTimeString()
                              ])->get();

                              if ($users->isEmpty()) {
                                  return 'Users not found';
                              }

                              $fp = fopen('php://temp', 'r+');

                              fputcsv($fp, ['Name', 'Email', 'Created At']);

                              foreach ($users as $user) {
                                  fputcsv($fp, [
                                      $user->name,
                                      $user->email,
                                      $user->created_at
                                  ]);
                              }

                              rewind($fp);

                              return stream_get_contents($fp);
                          });

        $response = Prism::text()
                         ->using(Provider::Ollama, 'qwen3:4b')
                         ->withClientOptions(['timeout' => 60])
                         ->withPrompt($input['prompt'])
                         ->withTools([$searchTool, $countTool, $dateSearchTool])
                         //->withToolChoice('search')
                         ->withMaxSteps(3)
                         ->asText();

        return $response->text;
    }
}
