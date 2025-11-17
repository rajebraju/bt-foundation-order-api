<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class ProductImport extends Model
{
    use HasFactory; 
    protected $fillable = ['filename','status','result'];
    protected $casts = ['result'=>'array'];
}
