<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ProductVariant;
use App\Models\Product;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'sku' => 'VAR-'.strtoupper($this->faker->unique()->bothify('??###')),
            'title' => $this->faker->word,
            'price' => $this->faker->randomFloat(2, 5, 300),
            'stock' => $this->faker->numberBetween(0, 100),
            'attributes' => ['color' => $this->faker->safeColorName()],
        ];
    }
}
