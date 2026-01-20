<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Member',
            'description' => 'Regular member',
            'is_system' => false,
        ];
    }

    /**
     * Indicate that the role is an administrator.
     */
    public function administrator(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Administrator',
            'description' => 'Full system access',
            'is_system' => true,
        ]);
    }
}
