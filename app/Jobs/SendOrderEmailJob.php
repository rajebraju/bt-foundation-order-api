<?php
namespace App\Jobs;

use App\Models\Order;
use App\Mail\OrderPlacedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;

class SendOrderEmailJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order->fresh('customer');
    }

    public function handle()
    {
        Mail::to($this->order->customer->email)->send(new OrderPlacedMail($this->order));
    }
}
