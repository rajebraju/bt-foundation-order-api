<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Orders", description: "Order management endpoints")]
class OrderController extends Controller
{
    protected OrderService $service;

    public function __construct(OrderService $service)
    {
        $this->middleware('auth:api');
        $this->service = $service;
    }

    #[OA\Get(
        path: "/orders",
        tags: ["Orders"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer", default: 20)),
            new OA\Parameter(name: "search", in: "query", schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: "200", description: "Order list"),
            new OA\Response(response: "401", description: "Unauthorized")
        ]
    )]
    public function index(Request $request)
    {
        $perPage = (int)$request->get('per_page', 20);
        $search = $request->get('search');

        $user = Auth::user();
        $query = Order::with('items.variant.product', 'customer');

        if ($user->hasRole('customer')) {
            $query->where('customer_id', $user->id);
        } elseif ($user->hasRole('vendor')) {
            $query->whereHas('items.variant.product', function ($q) use ($user) {
                $q->where('vendor_id', $user->id);
            });
        }

        if ($search) {
            $query->where('order_number', 'like', "%{$search}%");
        }

        $orders = $query->paginate($perPage);

        return response()->json([
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage(),
            ]
        ]);
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

    #[OA\Post(
        path: "/orders/{order}/cancel",
        tags: ["Orders"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "order", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: "200", description: "Order cancelled"),
            new OA\Response(response: "403", description: "Forbidden"),
            new OA\Response(response: "401", description: "Unauthorized")
        ]
    )]
    public function cancel(Order $order)
    {
        $user = Auth::user();

        if (!$user->hasRole('admin')) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $this->authorize('update', $order);
        $this->service->cancelOrder($order);

        return response()->json(['message' => 'Order cancelled']);
    }


    public function downloadInvoice(Order $order)
    {
        $this->authorize('view', $order);

        if (!$order->invoice_path) {
            return response()->json(['message' => 'Invoice not generated yet'], 404);
        }

        $file = storage_path('app/' . $order->invoice_path);

        if (!file_exists($file)) {
            return response()->json(['message' => 'Invoice file missing'], 404);
        }

        return response()->download($file);
    }
}
