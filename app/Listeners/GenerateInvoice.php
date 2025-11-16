<?php

namespace App\Listeners;

use App\Events\OrderConfirmed;
use App\Jobs\GenerateInvoicePdfJob;

class GenerateInvoice
{
    public function handle(OrderConfirmed $event)
    {
        // Dispatch job to queue
        GenerateInvoicePdfJob::dispatch($event->order);
    }
}
