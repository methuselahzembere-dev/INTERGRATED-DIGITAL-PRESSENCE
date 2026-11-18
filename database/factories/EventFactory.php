<?php

namespace Database\Factories;

use App\Models\Affiliate;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'affiliate_id' => Affiliate::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'venue' => fake()->city().' Community Hall',
            'starts_at' => now()->addDays(fake()->numberBetween(3, 60))->setTime(10, 0),
            'published_at' => now()->subDay(),
        ];
    }

    public function draft(): static
    {
        return $this->state(['published_at' => null]);
    }

    public function past(): static
    {
        return $this->state(['starts_at' => now()->subDays(fake()->numberBetween(3, 60))->setTime(10, 0)]);
    }
}
