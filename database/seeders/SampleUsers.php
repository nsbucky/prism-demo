<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;

class SampleUsers extends Seeder
{
    public function run(): void
    {
        User::truncate();

        User::factory()->make([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory()->create([
            'name' => 'Ollama',
            'email' => 'llama2@example.com',
        ]);

        User::factory()
            ->count(100)
            ->state(new Sequence(
                fn (Sequence $sequence) => [
                    'created_at' => Carbon::createFromTimestamp(rand(now()->startOfYear()->timestamp, time()))
                ]
            ))
            ->create();
    }
}
