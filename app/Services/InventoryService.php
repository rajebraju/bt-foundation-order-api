<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Deduct stock from a variant
     * @return bool success/failure
     */
    public function deductStock(ProductVariant $variant, int $quantity, ?int $orderId = null): bool
    {
        return DB::transaction(function () use ($variant, $quantity, $orderId) {
            $variant->refresh(); // fresh read
            if ($variant->stock < $quantity) {
                return false;
            }

            $variant->decrement('stock', $quantity);

            Inventory::create([
                'variant_id' => $variant->id,
                'order_id' => $orderId,
                'change' => -$quantity,
                'reason' => 'order_deducted',
            ]);

            return true;
        });
    }

    /**
     * Restore stock (e.g., on order cancellation)
     */
    public function restoreStock(ProductVariant $variant, int $quantity, ?int $orderId = null): bool
    {
        return DB::transaction(function () use ($variant, $quantity, $orderId) {
            $variant->increment('stock', $quantity);

            Inventory::create([
                'variant_id' => $variant->id,
                'order_id' => $orderId,
                'change' => $quantity,
                'reason' => 'order_cancelled',
            ]);

            return true;
        });
    }
}