<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $order->order_number }}</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 8px; border: 1px solid #ccc; }
        h2 { margin-bottom: 0; }
    </style>
</head>
<body>
<h2>Invoice #{{ $order->order_number }}</h2>

<p>
    <strong>Customer:</strong> {{ $customer->name }} <br>
    <strong>Date:</strong> {{ $order->created_at->format('d M Y') }}
</p>

<table>
    <thead>
        <tr>
            <th>Product</th>
            <th>Variant</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
    @foreach($items as $item)
        <tr>
            <td>{{ $item->product->name }}</td>
            <td>{{ $item->variant->title }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ number_format($item->unit_price,2) }}</td>
            <td>{{ number_format($item->line_total,2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<p>
    <strong>Subtotal:</strong> {{ number_format($order->subtotal,2) }} <br>
    <strong>Tax:</strong> {{ number_format($order->tax,2) }} <br>
    <strong>Shipping:</strong> {{ number_format($order->shipping,2) }} <br>
    <strong>Total:</strong> {{ number_format($order->total,2) }} <br>
</p>

</body>
</html>
