<?php
namespace App\Jobs;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

class GenerateInvoiceJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order->fresh('items.variant.product','customer');
    }

    public function handle()
    {
        $order = $this->order;
        $view = view('invoices.invoice', ['order' => $order])->render();
        $pdf = Pdf::loadHTML($view);
        $filename = "invoice_{$order->order_number}.pdf";
        $path = "invoices/{$filename}";
        Storage::put($path, $pdf->output());
        $order->invoice()->create(['filename' => $filename]);
    }
}
