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
    private const TITLES = [
        'Monthly General Meeting',
        'Membership Drive',
        'Community Outreach Day',
        'Leadership Training Workshop',
        'Youth Engagement Forum',
        'Annual Conference',
        'Strategy Planning Session',
        'Volunteer Orientation',
    ];

    private const VENUES = [
        'Harare International Conference Centre',
        'Bulawayo City Hall',
        'Mutare Civic Centre',
        'Gweru Showgrounds',
        'Masvingo Community Hall',
        'Chinhoyi Town Hall',
    ];

    public function definition(): array
    {
        return [
            'affiliate_id' => Affiliate::factory(),
            'title' => fake()->randomElement(self::TITLES),
            'description' => 'All members are encouraged to attend. Please arrive 30 minutes early and bring your membership details. Further information will be shared by your affiliate leadership.',
            'venue' => fake()->randomElement(self::VENUES),
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
