<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ProductVariant;
use App\Models\Product;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => 1,
            'sku' => $this->faker->unique()->lexify('VAR??????'),
            'title' => $this->faker->word(),
            'price' => $this->faker->randomFloat(2, 0, 50),
            'stock' => $this->faker->numberBetween(0, 100),
            'attributes' => ['size' => $this->faker->randomElement(['S', 'M', 'L'])],
        ];
    }
}
