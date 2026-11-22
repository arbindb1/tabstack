<?php

namespace Database\Factories;

use App\Models\DatabaseConnection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DatabaseConnection>
 */
class DatabaseConnectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(2, true),
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => fake()->word(),
            'username' => fake()->userName(),
            'password' => fake()->password(),
        ];
    }

    /**
     * Indicate that the connection uses SQLite.
     */
    public function sqlite(): static
    {
        return $this->state(fn (array $attributes) => [
            'driver' => 'sqlite',
            'host' => null,
            'port' => null,
            'database' => database_path('database.sqlite'),
            'username' => null,
            'password' => null,
        ]);
    }
}
