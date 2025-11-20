<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $vendor = User::where('email', 'vendor@email.com')->first();

        if (!$vendor) {
            $vendor = User::factory()->create([
                'email' => 'vendor@email.com'
            ]);
            if (method_exists($vendor, 'assignRole')) {
                $vendor->assignRole('vendor');
            }
        }

        // Create one product for tests
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'sku'       => 'PRD-TEST',
            'name'      => 'Test Product',
            'base_price'=> 100,
            'is_active' => true
        ]);

        // Create one variant with stock
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku'        => 'PRD-TEST-V1',
            'title'      => 'Default Variant',
            'price'      => 100,
            'stock'      => 50
        ]);

        // Create inventory
        $variant->inventory()->create([
            'quantity' => 50
        ]);
    }
}
