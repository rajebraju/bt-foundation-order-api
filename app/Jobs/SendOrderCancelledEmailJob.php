<?php
namespace App\Jobs;

use App\Models\Order;
use App\Mail\OrderCancelledMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;

class SendOrderCancelledEmailJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order->fresh('customer');
    }

    public function handle()
    {
        Mail::to($this->order->customer->email)->send(new OrderCancelledMail($this->order));
    }
}
