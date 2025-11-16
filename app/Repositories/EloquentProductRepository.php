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
        return $this->model->with('variants')->findOrFail($id);
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
                $variant->inventory()->create(['quantity' => $v['stock'] ?? 0]);
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
                    $variant->inventory()->create(['quantity' => $v['stock'] ?? 0]);
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
        return $this->model->with('variants.inventory')->paginate($perPage);
    }

    public function search(string $query, int $perPage = 15)
    {
        // MySQL fulltext fallback
        if (empty($query)) {
            return $this->paginate($perPage);
        }
        return $this->model
            ->whereRaw("MATCH(name,description) AGAINST(? IN NATURAL LANGUAGE MODE)", [$query])
            ->with('variants.inventory')
            ->paginate($perPage);
    }
}
