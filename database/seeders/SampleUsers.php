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

        User::factory()->create([
            'name' => 'Weird Al',
            'email' => 'weirdal@yankinit.gov',
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
