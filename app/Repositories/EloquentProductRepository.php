<?php
namespace App\Repositories;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class EloquentProductRepository implements ProductRepositoryInterface
{
    protected Product $model;

    public function __construct(Product $product)
    {
        $this->model = $product;
    }

    public function find(int $id)
    {
        return $this->model->with('variants.inventory')->findOrFail($id);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $variants = $data['variants'] ?? [];
            unset($data['variants']);
            $product = $this->model->create($data);
            foreach ($variants as $v) {
                $variant = $product->variants()->create([
                    'sku' => $v['sku'] ?? null,
                    'title' => $v['title'] ?? null,
                    'price' => $v['price'] ?? ($product->base_price ?? 0),
                    'stock' => $v['stock'] ?? 0,
                    'attributes' => $v['attributes'] ?? []
                ]);
                if (method_exists($variant, 'inventory')) {
                    $variant->inventory()->create(['quantity' => $v['stock'] ?? 0]);
                }
            }
            return $product->load('variants.inventory');
        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $product = $this->model->findOrFail($id);
            $variants = $data['variants'] ?? [];
            unset($data['variants']);
            $product->update($data);
            foreach ($variants as $v) {
                if (!empty($v['id'])) {
                    $variant = $product->variants()->find($v['id']);
                    if ($variant) {
                        $variant->update($v);
                        if (isset($v['stock'])) {
                            if ($variant->inventory) {
                                $variant->inventory->update(['quantity' => $v['stock']]);
                            } else {
                                $variant->inventory()->create(['quantity' => $v['stock']]);
                            }
                            $variant->stock = $v['stock'];
                            $variant->save();
                        }
                    }
                } else {
                    $variant = $product->variants()->create($v);
                    if (method_exists($variant, 'inventory')) {
                        $variant->inventory()->create(['quantity' => $v['stock'] ?? 0]);
                    }
                }
            }
            return $product->fresh('variants.inventory');
        });
    }

    public function delete(int $id)
    {
        $product = $this->model->findOrFail($id);
        return $product->delete();
    }

    public function paginate(int $perPage = 15)
    {
        $user = auth()->user();
        $isVendor = false;

        if ($user) {
            if (method_exists($user, 'hasRole')) {
                try {
                    if ($user->hasRole('vendor')) {
                        $isVendor = true;
                    }
                } catch (\Throwable $e) {
                    $isVendor = false;
                }
            }
            if (! $isVendor) {
                try {
                    if ($user->roles()->where('name', 'vendor')->exists()) {
                        $isVendor = true;
                    }
                } catch (\Throwable $e) {
                    $isVendor = $isVendor;
                }
            }
        }

        if ($isVendor && $user) {
            return $this->model
                ->where('vendor_id', $user->id)
                ->with('variants.inventory')
                ->paginate($perPage);
        }

        return $this->model->with('variants.inventory')->paginate($perPage);
    }

    public function search(string $query, int $perPage = 15)
    {
        if (empty($query)) {
            return $this->paginate($perPage);
        }
        return $this->model
            ->whereRaw("MATCH(name,description) AGAINST(? IN NATURAL LANGUAGE MODE)", [$query])
            ->with('variants.inventory')
            ->paginate($perPage);
    }
}
