<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['vendor_id', 'sku', 'name', 'description', 'base_price', 'is_active', 'metadata'];
    protected $casts = ['metadata' => 'array', 'is_active' => 'boolean'];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
}
