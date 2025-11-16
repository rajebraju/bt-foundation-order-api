<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GenerateInvoicePdfJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle()
    {
        $order = $this->order->load('customer', 'items.product', 'items.variant');

        // Create invoice HTML
        $pdf = Pdf::loadView('invoices.invoice-template', [
            'order' => $order,
            'items' => $order->items,
            'customer' => $order->customer
        ]);

        // File name
        $filename = 'invoice_' . $order->order_number . '.pdf';
        $path = storage_path('app/invoices/' . $filename);

        // Ensure directory exists
        if (!is_dir(storage_path('app/invoices'))) {
            mkdir(storage_path('app/invoices'), 0755, true);
        }

        // Save PDF file
        $pdf->save($path);

        // Save record in DB
        Invoice::updateOrCreate(
            ['order_id' => $order->id],
            ['filename' => $filename]
        );
    }
}
