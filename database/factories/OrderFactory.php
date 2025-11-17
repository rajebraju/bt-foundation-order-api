<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $customer = User::factory()->create();

        return [
            'order_number' => 'ORD-' . strtoupper($this->faker->bothify('####??')),
            'customer_id' => $customer->id,
            'subtotal' => $this->faker->randomFloat(2, 50, 500),
            'shipping' => $this->faker->randomFloat(2, 5, 30),
            'tax' => $this->faker->randomFloat(2, 2, 20),
            'total' => $this->faker->randomFloat(2, 60, 550),
            'status' => $this->faker->randomElement(['pending', 'processing']),
            'meta' => [],
        ];
    }
}