<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'cleaning_service' => fake()->randomElement(['End of Lease Clean', 'Commercial Office Clean', 'Carpet Steam Clean', 'Builders Clean']),
            'booking_date' => fake()->dateTimeBetween('-7 days', '+14 days')->format('Y-m-d'),
            'start_time' => '08:30',
            'finish_time' => '12:30',
            'technician' => fake()->name(),
            'priority' => fake()->randomElement(['Low', 'Normal', 'High', 'Urgent']),
            'status' => fake()->randomElement(['Pending', 'In Progress', 'Completed']),
            'internal_notes' => fake()->optional()->sentence(),
        ];
    }
}
