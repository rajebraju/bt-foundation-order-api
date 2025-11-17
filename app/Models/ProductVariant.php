<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class ProductVariant extends Model
{
    use HasFactory;
    protected $fillable = ['product_id','sku','title','price','stock','attributes'];
    protected $casts = ['attributes'=>'array'];

    public function product(){ return $this->belongsTo(Product::class); }
    public function inventory(){ return $this->hasOne(Inventory::class,'variant_id'); }
}
