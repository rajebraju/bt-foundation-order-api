<?php
namespace App\Events;

use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class OrderPlaced
{
    use SerializesModels;
    public Order $order;
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
