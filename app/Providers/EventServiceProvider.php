<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \App\Events\OrderPlaced::class => [
            \App\Listeners\HandleOrderPlaced::class,
        ],
        \App\Events\OrderCancelled::class => [
            \App\Listeners\HandleOrderCancelled::class,
        ],
        \App\Events\LowStock::class => [
            \App\Listeners\SendLowStockAlert::class,
        ],
        \App\Events\OrderConfirmed::class => [
            \App\Listeners\GenerateInvoice::class,
        ],
    ];
}
