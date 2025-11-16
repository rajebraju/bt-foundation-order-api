<?php
namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPlacedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order->load('items.variant.product');
    }

    public function build()
    {
        return $this->subject("Order {$this->order->order_number} placed")
                    ->view('emails.order_placed')
                    ->with(['order' => $this->order]);
    }
}
