<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    protected $repo;

    public function __construct(ProductRepositoryInterface $repo)
    {
        $this->repo = $repo;
        // public index/search allowed; write actions require auth
        $this->middleware('auth:api')->except(['index','show','search']);
    }

    public function index(Request $request)
    {
        $perPage = (int)$request->get('per_page', 15);
        $res = $this->repo->paginate($perPage);
        return response()->json($res);
    }

    public function show($id)
    {
        $product = $this->repo->find($id);
        return response()->json($product);
    }

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

    public function update(Request $request, $id)
    {
        $this->authorize('update', \App\Models\Product::class);
        $data = $request->only(['sku','name','description','base_price','variants']);
        $product = $this->repo->update($id, $data);
        return response()->json($product);
    }

    public function destroy($id)
    {
        $this->authorize('delete', \App\Models\Product::class);
        $this->repo->delete($id);
        return response()->json(['message'=>'deleted']);
    }

    public function search(Request $request)
    {
        $q = $request->get('q','');
        $perPage = (int)$request->get('per_page',15);
        $res = $this->repo->search($q, $perPage);
        return response()->json($res);
    }
}
