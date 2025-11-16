<?php
namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Jobs\GenerateInvoiceJob;
use App\Jobs\SendOrderEmailJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleOrderPlaced implements ShouldQueue
{
    public function handle(OrderPlaced $event)
    {
        // Generate invoice and send email asynchronously
        GenerateInvoiceJob::dispatch($event->order)->onQueue('invoices');
        SendOrderEmailJob::dispatch($event->order)->onQueue('emails');
    }
}
