<?php
namespace App\Listeners;

use App\Events\LowStock;
use App\Jobs\SendLowStockNotificationJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendLowStockAlert implements ShouldQueue
{
    public function handle(LowStock $event)
    {
        SendLowStockNotificationJob::dispatch($event->variant)->onQueue('notifications');
    }
}
