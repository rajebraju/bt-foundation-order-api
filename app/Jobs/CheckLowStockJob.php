<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\ProductVariant;

class CheckLowStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $threshold = config('inventory.low_stock_threshold', 5);

        $low = ProductVariant::whereColumn('stock', '<=', 'stock') // placeholder; real check below
            ->where('stock', '<=', $threshold)
            ->get();

        if ($low->isNotEmpty()) {
            // dispatch the notification job with the collection
            SendLowStockNotificationJob::dispatch($low);
        }
    }
}
