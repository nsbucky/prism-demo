<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Song>
 */
class SongFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'keywords' => $this->faker->words(3, true),
            'lyrics' => $this->faker->paragraphs(3, true),
            'prompt' => $this->faker->sentence(5),
            'formatted_prompt' => $this->faker->sentence(5),
            'matched_lyrics' => [],
        ];
    }
}
