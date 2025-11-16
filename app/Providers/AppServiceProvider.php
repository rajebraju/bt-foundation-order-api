<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProductRepositoryInterface;
use App\Repositories\EloquentProductRepository;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
    }

    public function boot()
    {
        // optionally publish or merge config
        $this->mergeConfigFrom(__DIR__ . '/../../config/inventory.php', 'inventory');
    }
}
