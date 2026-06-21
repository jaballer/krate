<?php

namespace Database\Factories;

use App\Enums\RecordCondition;
use App\Enums\RecordFormat;
use App\Enums\RecordSpeed;
use App\Models\Record;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Record>
 */
class RecordFactory extends Factory
{
    protected $model = Record::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'artist' => fake()->name(),
            'genre' => fake()->randomElement(['Hip Hop', 'Rap', 'Jazz', 'Soul', 'Funk']),
            'release_year' => fake()->numberBetween(1960, 2025),
            'label' => fake()->company(),
            'catalog_number' => fake()->bothify('??####'),
            'format' => fake()->randomElement(RecordFormat::cases()),
            'speed' => fake()->randomElement(RecordSpeed::cases()),
            'condition' => fake()->randomElement(RecordCondition::cases()),
            'purchase_date' => fake()->date(),
            'purchase_price' => fake()->randomFloat(2, 5, 100),
            'notes' => fake()->optional()->paragraph(),
            'front_image' => null,
            'back_image' => null,
            'purchase_link' => fake()->optional()->url(),
            'audio_file_url' => null,
            'bpm' => fake()->optional()->numberBetween(60, 180),
        ];
    }
}
