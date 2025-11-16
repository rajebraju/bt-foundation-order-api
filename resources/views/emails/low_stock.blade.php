<!doctype html>
<html>
<head><meta charset="utf-8"><title>Low Stock Alert</title></head>
<body>
<p>Product: {{ $variant->product->name }} ({{ $variant->sku }})</p>
<p>Current stock: {{ $variant->stock }}</p>
<p>Please restock.</p>
</body>
</html>
