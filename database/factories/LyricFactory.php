<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lyric>
 */
class LyricFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'          => $this->faker->word,
            'embeddings'    => $this->faker->randomElement(['embedding1', 'embedding2', 'embedding3']),
            'original_text' => $this->faker->sentence,
        ];
    }
}
