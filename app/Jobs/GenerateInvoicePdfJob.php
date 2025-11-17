<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use App\Models\Order;

class GenerateInvoicePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle()
    {
        $order = $this->order->fresh();
        $filename = "{$order->order_number}.pdf";
        $relative = "invoices/{$filename}";

        $content = "Invoice for order {$order->order_number}\nTotal: {$order->total}";

        Storage::disk('local')->put($relative, $content);

        $order->invoice_path = $relative;
        $order->save();
    }
}
