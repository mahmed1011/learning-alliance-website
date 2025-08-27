<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductSizeItem extends Model
{
    use HasFactory;

     // Optionally, define fillable properties
    protected $fillable = ['size', 'status', 'position'];


    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}
