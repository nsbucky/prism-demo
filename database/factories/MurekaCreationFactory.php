<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MurekaCreation>
 */
class MurekaCreationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'song_id'       => null, // Will be set in the test
            'mureka_id'     => $this->faker->uuid,
            'model'         => $this->faker->randomElement(['model1', 'model2', 'model3']),
            'status'        => $this->faker->randomElement(['pending', 'processing', 'completed', 'failed']),
            'failed_reason' => $this->faker->optional()->sentence,
            'finished_at'   => $this->faker->optional()->dateTime,
            'failed_at'     => $this->faker->optional()->dateTime,
            'choices'       => [
                [
                    'url'      => $this->faker->url,
                    'flac_url' => $this->faker->optional()->url,
                    'duration' => $this->faker->numberBetween(30, 300),
                ],
            ],
        ];
    }
}
