<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ProductsCsvImport implements ToCollection
{
    /**
     * Expects CSV with columns:
     * sku,name,description,base_price,variant_sku,variant_title,variant_price,variant_stock,variant_attributes_json
     */
    public function collection(Collection $rows)
    {
        // ignore header row if present - caller may pass config
        foreach ($rows as $index => $row) {
            // skip empty rows
            if ($row->filter()->isEmpty()) continue;

            $sku = trim($row[0] ?? '');
            if (empty($sku)) continue;

            $product = Product::firstOrCreate(
                ['sku' => $sku],
                [
                    'name' => $row[1] ?? 'Unnamed',
                    'description' => $row[2] ?? null,
                    'base_price' => (float)($row[3] ?? 0),
                    'is_active' => true
                ]
            );

            // variant columns
            $variantSku = trim($row[4] ?? '');
            if (!empty($variantSku)) {
                $attributes = null;
                try {
                    $attributes = json_decode($row[8] ?? '', true) ?: null;
                } catch (\Throwable $e) {
                    $attributes = null;
                }
                $variant = $product->variants()->updateOrCreate(
                    ['sku' => $variantSku],
                    [
                        'title' => $row[5] ?? null,
                        'price' => (float)($row[6] ?? $product->base_price),
                        'stock' => (int)($row[7] ?? 0),
                        'attributes' => $attributes
                    ]
                );

                if ($variant->inventory) {
                    $variant->inventory->update(['quantity' => $variant->stock]);
                } else {
                    $variant->inventory()->create(['quantity' => $variant->stock]);
                }
            }
        }
    }
}
