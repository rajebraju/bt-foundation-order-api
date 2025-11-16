<!doctype html>
<html>
<head><meta charset="utf-8"><title>Order Cancelled</title></head>
<body>
<h3>Order {{ $order->order_number }} Cancelled</h3>
<p>Dear {{ $order->customer->name }},</p>
<p>Your order has been cancelled. If you need help, contact support.</p>
</body>
</html>
