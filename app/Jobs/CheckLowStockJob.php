<?php

namespace App\Jobs;

use App\Models\ProductVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckLowStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Threshold: 5 units (configurable)
        $threshold = 5;

        $lowStockVariants = ProductVariant::where('stock', '<', $threshold)
            ->with('product')
            ->get();

        if ($lowStockVariants->isNotEmpty()) {
            SendLowStockNotificationJob::dispatch($lowStockVariants);
        }
    }
}