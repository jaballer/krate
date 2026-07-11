<?php

namespace Database\Factories;

use App\Models\Track;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Track>
 */
class TrackFactory extends Factory
{
    protected $model = Track::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'artist' => fake()->name(),
            'album' => fake()->optional()->sentence(2),
            'genre' => fake()->randomElement(['Hip Hop', 'Rap', 'Jazz', 'Soul', 'Funk']),
            'release_year' => fake()->numberBetween(1960, 2025),
            'duration_seconds' => fake()->numberBetween(90, 420),
            'bpm' => fake()->optional()->numberBetween(60, 180),
            'audio_file_url' => null,
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
