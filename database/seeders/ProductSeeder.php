<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $vendor = \App\Models\User::role('vendor')->first();
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'sku' => 'PRD-001',
            'name' => 'Test T-Shirt',
            'description' => 'Comfortable cotton t-shirt',
            'base_price' => 199.99,
            'is_active' => true
        ]);

        $variant = $product->variants()->create([
            'sku' => 'PRD-001-BL-S',
            'title' => 'Blue - Small',
            'price' => 199.99,
            'stock' => 25,
            'attributes' => ['color'=>'blue','size'=>'S']
        ]);
        $variant->inventory()->create(['quantity'=>25]);
    }
}
