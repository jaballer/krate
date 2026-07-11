<?php

namespace Database\Factories;

use App\Enums\TrackSide;
use App\Models\Record;
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
        // Standalone by default (record_id/side/position null); use forRecord()
        // to place a track on a record's tracklist.
        return [
            'title' => fake()->sentence(3),
            'artist' => fake()->name(),
            'image' => null,
            'album' => fake()->optional()->sentence(2),
            'genre' => fake()->randomElement(['Hip Hop', 'Rap', 'Jazz', 'Soul', 'Funk']),
            'release_year' => fake()->numberBetween(1960, 2025),
            'duration_seconds' => fake()->numberBetween(90, 420),
            'bpm' => fake()->optional()->numberBetween(60, 180),
            'audio_file_url' => null,
            'notes' => fake()->optional()->paragraph(),
        ];
    }

    /** Place this track on a record's tracklist at the given side/position. */
    public function forRecord(Record $record, ?TrackSide $side = null, ?int $position = null): static
    {
        return $this->state(fn (): array => [
            'record_id' => $record->id,
            'side' => $side,
            'position' => $position,
        ]);
    }
}
