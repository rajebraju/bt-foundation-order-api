<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'vendor_id' => null,
            'sku' => 'PRD-'.strtoupper($this->faker->unique()->bothify('??###')),
            'name' => $this->faker->productName ?? $this->faker->word,
            'description' => $this->faker->sentence,
            'base_price' => $this->faker->randomFloat(2, 10, 500),
            'is_active' => true,
        ];
    }
}
