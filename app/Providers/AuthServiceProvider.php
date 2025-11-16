<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Order;
use App\Models\Product;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Order::class => OrderPolicy::class,
        Product::class => ProductPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
