<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;
use App\Models\User;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'vendor_id' => User::factory(),
            'sku' => 'PRD-' . strtoupper($this->faker->unique()->bothify('????##')),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'base_price' => $this->faker->randomFloat(2, 10, 500),
            'is_active' => true,
            'meta' => [],
        ];
    }
}
