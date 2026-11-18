<?php

namespace Database\Seeders;

use App\Models\Affiliate;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Demo data only (Faker, no real people). All accounts use the password "password".
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create(['name' => 'National Admin', 'email' => 'national.admin@idp.test'])->assignRole('national_admin');
        User::factory()->create(['name' => 'National User', 'email' => 'national.user@idp.test'])->assignRole('national_user');

        foreach (Affiliate::all() as $affiliate) {
            $slug = Str::slug($affiliate->name);

            $admin = User::factory()->create([
                'name' => $affiliate->name.' Admin',
                'email' => "admin@{$slug}.idp.test",
                'affiliate_id' => $affiliate->id,
            ]);
            $admin->assignRole('affiliate_admin');

            $members = collect(range(1, 20))->map(function (int $i) use ($affiliate, $slug) {
                $member = User::factory()->create([
                    'email' => "member{$i}@{$slug}.idp.test",
                    'affiliate_id' => $affiliate->id,
                    // spread registrations across the last 12 months
                    'created_at' => now()->subDays(fake()->numberBetween(0, 360)),
                ]);
                $member->assignRole('affiliate_user');

                return $member;
            });

            $events = collect([
                Event::factory()->create(['affiliate_id' => $affiliate->id, 'created_by' => $admin->id]),
                Event::factory()->create(['affiliate_id' => $affiliate->id, 'created_by' => $admin->id]),
                Event::factory()->past()->create(['affiliate_id' => $affiliate->id, 'created_by' => $admin->id]),
                Event::factory()->draft()->create(['affiliate_id' => $affiliate->id, 'created_by' => $admin->id]),
            ]);

            foreach ($events->take(3) as $event) {
                foreach ($members->random(8) as $member) {
                    EventRegistration::create(['event_id' => $event->id, 'user_id' => $member->id, 'status' => 'confirmed']);
                }
            }
        }
    }
}
