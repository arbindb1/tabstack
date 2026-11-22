<?php

namespace Database\Factories;

use App\Models\DatabaseConnection;
use App\Models\Tab;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tab>
 */
class TabFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'database_connection_id' => DatabaseConnection::factory(),
            'type' => 'table',
            'label' => fake()->word(),
            'table_name' => fake()->word(),
            'sql_body' => null,
            'position' => 0,
            'is_active' => false,
        ];
    }

    /**
     * Indicate that the tab is a SQL editor tab.
     */
    public function sql(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'sql',
            'table_name' => null,
            'sql_body' => '',
        ]);
    }
}
