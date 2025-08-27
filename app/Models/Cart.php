<?php

// app/Models/Cart.php
namespace App\Models;

use App\Models\Product;
use App\Models\ProductSize;
use App\Models\ProductSizeItem;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'session_id',
        'product_id',
        'size_id',
        'product_size_id',
        'quantity',
        'subtotal',
    ];

    // Relations
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function sizeItem() // product_size_items
    {
        return $this->belongsTo(ProductSizeItem::class, 'size_id');
    }

    public function variant()  // product_sizes
    {
        return $this->belongsTo(ProductSize::class, 'product_size_id');
    }
}
