<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;
use App\Models\Product;

#[OA\Tag(name: "Products", description: "Product management endpoints")]
class ProductController extends Controller
{
    protected $repo;

    public function __construct(ProductRepositoryInterface $repo)
    {
        $this->repo = $repo;
        $this->middleware('auth:api')->except(['index','show','search']);
    }

    #[OA\Get(
        path: "/products",
        tags: ["Products"],
        parameters: [
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer", default: 15))
        ],
        responses: [
            new OA\Response(response: "200", description: "Product list")
        ]
    )]
    public function index(Request $request)
    {
        $perPage = (int)$request->get('per_page', 15);
        $user = Auth::user();

        // 🔥 FIX (VendorProductTest): Vendor sees only own products
        if ($user && $user->hasRole('vendor')) {
            $products = Product::where('vendor_id', $user->id)
                ->with('variants.inventory')
                ->paginate($perPage);

            return response()->json($products);
        }

        // others use repository
        $res = $this->repo->paginate($perPage);
        return response()->json($res);
    }

    #[OA\Get(
        path: "/products/{id}",
        tags: ["Products"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: "200", description: "Product details"),
            new OA\Response(response: "404", description: "Not found")
        ]
    )]
    public function show($id)
    {
        $product = $this->repo->find($id);
        return response()->json($product);
    }

    #[OA\Post(
        path: "/products",
        tags: ["Products"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    required: ["sku", "name", "base_price"],
                    properties: [
                        new OA\Property(property: "sku", type: "string"),
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "description", type: "string", nullable: true),
                        new OA\Property(property: "base_price", type: "number"),
                        new OA\Property(
                            property: "variants",
                            type: "array",
                            nullable: true,
                            items: new OA\Items()
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: "201", description: "Product created"),
            new OA\Response(response: "401", description: "Unauthorized")
        ]
    )]
    public function store(Request $request)
    {
        $this->authorize('create', \App\Models\Product::class);

        $data = $request->validate([
            'sku'=>'required|unique:products,sku',
            'name'=>'required|string',
            'description'=>'nullable|string',
            'base_price'=>'required|numeric',
            'variants'=>'array|nullable',
            'variants.*.sku'=>'nullable',
            'variants.*.title'=>'nullable',
            'variants.*.price'=>'nullable|numeric',
            'variants.*.stock'=>'nullable|integer',
            'variants.*.attributes'=>'nullable|array',
        ]);

        $data['vendor_id'] = Auth::id();

        $product = $this->repo->create($data);

        return response()->json($product, 201);
    }

    #[OA\Put(
        path: "/products/{id}",
        tags: ["Products"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema()
            )
        ),
        responses: [
            new OA\Response(response: "200", description: "Product updated"),
            new OA\Response(response: "401", description: "Unauthorized")
        ]
    )]
    public function update(Request $request, $id)
    {
        $this->authorize('update', \App\Models\Product::class);

        $data = $request->only(['sku','name','description','base_price','variants']);
        $product = $this->repo->update($id, $data);

        return response()->json($product);
    }

    #[OA\Delete(
        path: "/products/{id}",
        tags: ["Products"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: "200", description: "Product deleted"),
            new OA\Response(response: "401", description: "Unauthorized")
        ]
    )]
    public function destroy($id)
    {
        $this->authorize('delete', \App\Models\Product::class);
        $this->repo->delete($id);
        return response()->json(['message'=>'deleted']);
    }

    #[OA\Get(
        path: "/products/search",
        tags: ["Products"],
        parameters: [
            new OA\Parameter(name: "q", in: "query", required: true, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer", default: 15))
        ],
        responses: [
            new OA\Response(response: "200", description: "Search results")
        ]
    )]
    public function search(Request $request)
    {
        $q = $request->get('q','');
        $perPage = (int)$request->get('per_page',15);
        $res = $this->repo->search($q, $perPage);
        return response()->json($res);
    }
}
