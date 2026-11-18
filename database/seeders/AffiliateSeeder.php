<?php

namespace Database\Seeders;

use App\Models\Affiliate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AffiliateSeeder extends Seeder
{
    /**
     * Demonstration affiliates. Spellings of 'Citizens 4ED' and 'Journalists 4ED'
     * were corrected from the brief; add more affiliates here (or in the panel) later.
     */
    public const NAMES = [
        'Boys Dzamudhara',
        'Nurse Aides 4ED',
        'Patriotic Churches in Zimbabwe',
        'Women Empowerment Forum',
        'Citizens 4ED',
        'Journalists 4ED',
        'Zimbabwe United Churches',
        'Musician 4ED',
    ];

    public function run(): void
    {
        foreach (self::NAMES as $name) {
            Affiliate::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'is_active' => true]);
        }
    }
}
