<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Events\OrderPlaced;
use App\Events\OrderCancelled;
use App\Events\OrderConfirmed;
use App\Events\LowStock;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    public function createOrder(array $payload, $customer)
    {
        return DB::transaction(function () use ($payload, $customer) {
            $items = $payload['items'] ?? [];
            if (empty($items)) {
                throw new Exception("No items provided");
            }

            $subtotal = 0.00;
            $order = Order::create([
                'order_number' => strtoupper('ORD-' . Str::random(10)),
                'customer_id' => $customer->id,
                'subtotal' => 0,
                'shipping' => $payload['shipping'] ?? 0,
                'tax' => $payload['tax'] ?? 0,
                'total' => 0,
                'status' => 'pending',
            ]);

            foreach ($items as $it) {
                $variant = ProductVariant::lockForUpdate()->findOrFail($it['variant_id']);
                if ($variant->stock < $it['quantity']) {
                    throw new Exception("Insufficient stock for variant {$variant->sku}");
                }
                $price = $variant->price;
                $lineTotal = round($price * $it['quantity'], 2);
                $subtotal += $lineTotal;
                $order->items()->create([
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->id,
                    'quantity' => $it['quantity'],
                    'unit_price' => $price,
                    'line_total' => $lineTotal,
                ]);
            }

            $order->subtotal = round($subtotal, 2);
            $order->total = round($order->subtotal + ($order->shipping ?? 0) + ($order->tax ?? 0), 2);
            $order->save();

            event(new OrderPlaced($order));

            return $order->load('items');
        });
    }

    public function confirmOrder(Order $order)
    {
        return DB::transaction(function () use ($order) {
            if ($order->status !== 'pending') {
                throw new Exception("Only pending orders can be confirmed");
            }

            foreach ($order->items as $item) {
                $variant = ProductVariant::lockForUpdate()->findOrFail($item->variant_id);
                if ($variant->stock < $item->quantity) {
                    throw new Exception("Insufficient stock for variant {$variant->sku}");
                }

                // atomic decrement
                $variant->decrement('stock', $item->quantity);

                if ($variant->inventory) {
                    $variant->inventory->decrement('quantity', $item->quantity);
                }

                // reload variant to get latest stock after decrement (if needed)
                $variant->refresh();

                if ($variant->stock <= config('inventory.low_stock_threshold', 5)) {
                    event(new LowStock($variant));
                }
            }

            $order->status = 'processing';
            $order->save();

            // Dispatch OrderConfirmed event so invoice generation triggers
            event(new OrderConfirmed($order));

            return $order;
        });
    }

    public function cancelOrder(Order $order)
    {
        return DB::transaction(function () use ($order) {
            if (in_array($order->status, ['shipped', 'delivered'])) {
                throw new Exception("Cannot cancel shipped or delivered orders");
            }

            $order->status = 'cancelled';
            $order->save();

            // Fire event; listener(s) should restore inventory once.
            event(new OrderCancelled($order));

            return $order;
        });
    }
}
