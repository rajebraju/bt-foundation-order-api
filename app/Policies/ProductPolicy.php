<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Product;

class ProductPolicy
{
    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user = null, Product $product)
    {
        return $product->is_active || ($user && $user->hasRole('admin')) || ($user && $user->id == $product->vendor_id);
    }

    public function create(User $user)
    {
        return $user->hasAnyRole(['admin','vendor']);
    }

    public function update(User $user, Product $product)
    {
        return $user->hasRole('admin') || ($user->hasRole('vendor') && $product->vendor_id == $user->id);
    }

    public function delete(User $user, Product $product)
    {
        return $user->hasRole('admin') || ($user->hasRole('vendor') && $product->vendor_id == $user->id);
    }
}
