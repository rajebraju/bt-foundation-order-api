<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use App\Http\Services\InventoryService;

class OrderController extends Controller
{
    protected OrderService $service;

    public function __construct(OrderService $service)
    {
        $this->middleware('auth:api');
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $perPage = (int)$request->get('per_page', 20);
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            $orders = Order::with('items.variant.product', 'customer')->paginate($perPage);
        } elseif ($user->hasRole('vendor')) {
            // vendor: get orders that include their products
            $orders = Order::whereHas('items.variant.product', function ($q) use ($user) {
                $q->where('vendor_id', $user->id);
            })->with('items.variant.product', 'customer')->paginate($perPage);
        } else {
            $orders = $user->orders()->with('items.variant.product')->paginate($perPage);
        }
        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|integer|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping' => 'nullable|numeric',
            'tax' => 'nullable|numeric',
        ]);
        $order = $this->service->createOrder($data, $request->user());
        return response()->json($order, 201);
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);
        return response()->json($order->load('items.variant.product', 'customer'));
    }

    public function confirm(Order $order)
    {
        $this->authorize('update', $order);
        $this->service->confirmOrder($order);
        return response()->json(['message' => 'Order confirmed']);
    }

    public function cancel(Order $order)
    {
        $this->authorize('update', $order);
        $this->service->cancelOrder($order);
        return response()->json(['message' => 'Order cancelled']);
    }

    public function downloadInvoice(Order $order)
    {
        $this->authorize('view', $order);

        if (!$order->invoice) {
            return response()->json(['message' => 'Invoice not generated yet'], 404);
        }

        $file = storage_path('app/invoices/' . $order->invoice->filename);

        if (!file_exists($file)) {
            return response()->json(['message' => 'Invoice file missing'], 404);
        }

        return response()->download($file);
    }
}
