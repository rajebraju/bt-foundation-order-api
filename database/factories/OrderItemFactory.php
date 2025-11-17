<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        return [
            'order_id' => null,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => $this->faker->numberBetween(1, 5),
            'unit_price' => $variant->price + $product->base_price,
            'line_total' => function (array $attributes) {
                return $attributes['quantity'] * $attributes['unit_price'];
            },
            'meta' => [],
        ];
    }
}