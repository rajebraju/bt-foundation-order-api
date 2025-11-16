<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Order;

class OrderPolicy
{
    public function view(User $user, Order $order)
    {
        if ($user->hasRole('admin')) return true;
        if ($user->hasRole('vendor')) {
            // vendor can view orders that include their products
            return $order->items()->whereHas('variant.product', function($q) use ($user){
                $q->where('vendor_id', $user->id);
            })->exists();
        }
        return $order->customer_id === $user->id;
    }

    public function update(User $user, Order $order)
    {
        if ($user->hasRole('admin')) return true;
        if ($user->hasRole('vendor')) {
            // allow vendor to confirm shipments for their orders (customize)
            return $order->items()->whereHas('variant.product', function($q) use ($user){
                $q->where('vendor_id', $user->id);
            })->exists();
        }
        return $order->customer_id === $user->id;
    }
}
