<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['vendor_id','sku','name','description','base_price','is_active','metadata'];
    protected $casts = ['metadata'=>'array','is_active'=>'boolean'];

    public function variants(){ return $this->hasMany(ProductVariant::class); }
    public function vendor(){ return $this->belongsTo(User::class,'vendor_id'); }
}
