<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    protected $model = Setting::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'setting_key' => fake()->unique()->slug(2),
            'setting_value' => fake()->word(),
            'setting_type' => 'string',
            'category' => 'general',
            'description' => fake()->sentence(),
            'is_private' => false,
        ];
    }
}
