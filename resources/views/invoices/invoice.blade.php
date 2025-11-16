<!doctype html>
<html>
<head><meta charset="utf-8"><title>Invoice {{ $order->order_number }}</title>
<style>
body { font-family: DejaVu Sans, sans-serif; font-size:12px; }
table { width:100%; border-collapse: collapse; }
table, th, td { border: 1px solid #ddd; padding: 8px; }
</style>
</head>
<body>
<h1>Invoice - {{ $order->order_number }}</h1>
<p>Date: {{ $order->created_at->toDateString() }}</p>
<p>Customer: {{ $order->customer->name }} ({{ $order->customer->email }})</p>
<table>
<thead><tr><th>Item</th><th>Qty</th><th>Unit Price</th><th>Line Total</th></tr></thead>
<tbody>
@foreach($order->items as $i)
<tr>
<td>{{ $i->variant->title ?? $i->product->name }}</td>
<td>{{ $i->quantity }}</td>
<td>{{ number_format($i->unit_price,2) }}</td>
<td>{{ number_format($i->line_total,2) }}</td>
</tr>
@endforeach
</tbody>
</table>
<p>Subtotal: {{ number_format($order->subtotal,2) }}</p>
<p>Shipping: {{ number_format($order->shipping,2) }}</p>
<p>Tax: {{ number_format($order->tax,2) }}</p>
<h3>Total: {{ number_format($order->total,2) }}</h3>
</body>
</html>
