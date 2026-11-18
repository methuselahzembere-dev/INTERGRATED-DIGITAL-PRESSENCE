<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    private const FIRST_NAMES = [
        'Tendai', 'Farai', 'Tatenda', 'Rudo', 'Chipo', 'Tafadzwa', 'Nyasha', 'Blessing', 'Kudzai', 'Rumbidzai',
        'Tinashe', 'Memory', 'Simbarashe', 'Shamiso', 'Takudzwa', 'Vimbai', 'Munashe', 'Ruvimbo', 'Panashe', 'Tsitsi',
        'Sipho', 'Nomsa', 'Thabo', 'Bongani', 'Thandiwe', 'Ndumiso', 'Nkosana', 'Sibusiso', 'Lindiwe', 'Themba',
        'Nobuhle', 'Mthulisi', 'Zodwa', 'Busisiwe', 'Khumbulani',
    ];

    private const SURNAMES = [
        'Moyo', 'Ncube', 'Sibanda', 'Dube', 'Ndlovu', 'Nkomo', 'Mpofu', 'Khumalo', 'Moyo', 'Tshuma',
        'Chikwanha', 'Moyo', 'Mutasa', 'Chigumba', 'Makoni', 'Mandaza', 'Chinamasa', 'Mukanya', 'Gumbo', 'Zvobgo',
        'Mapfumo', 'Chimbira', 'Chitsiga', 'Marufu', 'Mashingaidze', 'Nyathi', 'Maphosa', 'Mahlangu', 'Hove', 'Mavhunga',
    ];

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(self::FIRST_NAMES).' '.fake()->randomElement(self::SURNAMES),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
