<!doctype html>
<html>
<head><meta charset="utf-8"><title>Order Placed</title></head>
<body>
<h2>Thank you — Order {{ $order->order_number }} received</h2>
<p>Hi {{ $order->customer->name }},</p>
<p>We have received your order. Summary:</p>
<ul>
@foreach($order->items as $item)
    <li>{{ $item->quantity }} x {{ $item->variant->title ?? $item->product->name }} — {{ number_format($item->line_total,2) }}</li>
@endforeach
</ul>
<p>Subtotal: {{ number_format($order->subtotal,2) }}<br>
Total: {{ number_format($order->total,2) }}</p>
</body>
</html>
