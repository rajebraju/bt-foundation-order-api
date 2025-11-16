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
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer", default: 20))
        ],
        responses: [
            new OA\Response(response: "200", description: "Order list"),
            new OA\Response(response: "401", description: "Unauthorized")
        ]
    )]
    public function index(Request $request)
    {
        $perPage = (int)$request->get('per_page', 20);
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            $orders = Order::with('items.variant.product', 'customer')->paginate($perPage);
        } elseif ($user->hasRole('vendor')) {
            $orders = Order::whereHas('items.variant.product', function ($q) use ($user) {
                $q->where('vendor_id', $user->id);
            })->with('items.variant.product', 'customer')->paginate($perPage);
        } else {
            $orders = $user->orders()->with('items.variant.product')->paginate($perPage);
        }
        return response()->json($orders);
    }

    #[OA\Post(
        path: "/orders",
        tags: ["Orders"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    required: ["items"],
                    properties: [
                        new OA\Property(
                            property: "items",
                            type: "array",
                            items: new OA\Items(
                                required: ["variant_id", "quantity"],
                                properties: [
                                    new OA\Property(property: "variant_id", type: "integer"),
                                    new OA\Property(property: "quantity", type: "integer")
                                ]
                            )
                        ),
                        new OA\Property(property: "shipping", type: "number", nullable: true),
                        new OA\Property(property: "tax", type: "number", nullable: true)
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: "201", description: "Order created"),
            new OA\Response(response: "401", description: "Unauthorized")
        ]
    )]
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

    #[OA\Get(
        path: "/orders/{order}",
        tags: ["Orders"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "order", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: "200", description: "Order details"),
            new OA\Response(response: "401", description: "Unauthorized"),
            new OA\Response(response: "404", description: "Not found")
        ]
    )]
    public function show(Order $order)
    {
        $this->authorize('view', $order);
        return response()->json($order->load('items.variant.product', 'customer'));
    }

    #[OA\Post(
        path: "/orders/{order}/confirm",
        tags: ["Orders"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "order", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: "200", description: "Order confirmed"),
            new OA\Response(response: "401", description: "Unauthorized")
        ]
    )]
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
            new OA\Response(response: "401", description: "Unauthorized")
        ]
    )]
    public function cancel(Order $order)
    {
        $this->authorize('update', $order);
        $this->service->cancelOrder($order);
        return response()->json(['message' => 'Order cancelled']);
    }

    #[OA\Get(
        path: "/orders/{order}/invoice",
        tags: ["Orders"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "order", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: "200", description: "PDF invoice file"),
            new OA\Response(response: "404", description: "Invoice not found")
        ]
    )]
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