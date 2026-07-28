<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_name' => fake()->name(),
            'company' => fake()->optional()->company(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'address' => fake()->streetAddress(),
            'suburb' => fake()->city(),
            'state' => fake()->randomElement(['NT', 'QLD', 'NSW', 'VIC', 'SA', 'WA']),
            'postcode' => fake()->postcode(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
