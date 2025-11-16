<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProductImport extends Model
{
    protected $fillable = ['filename','status','result'];
    protected $casts = ['result'=>'array'];
}
