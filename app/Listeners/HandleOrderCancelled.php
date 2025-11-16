<?php
namespace App\Listeners;

use App\Events\OrderCancelled;
use App\Jobs\SendOrderCancelledEmailJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleOrderCancelled implements ShouldQueue
{
    public function handle(OrderCancelled $event)
    {
        SendOrderCancelledEmailJob::dispatch($event->order)->onQueue('emails');
    }
}
