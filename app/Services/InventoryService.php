<?php

namespace App\Http\Services;
use App\Models\ProductVariant;

class InventoryService
{
    public function deduct($variantId, $quantity)
    {
        $variant = ProductVariant::findOrFail($variantId);
        if ($variant->stock_quantity < $quantity) {
            throw new \Exception('Insufficient stock');
        }
        $variant->decrement('stock_quantity', $quantity);
    }

    public function restore($variantId, $quantity)
    {
        ProductVariant::where('id', $variantId)->increment('stock_quantity', $quantity);
    }
}