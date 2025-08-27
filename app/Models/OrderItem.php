<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id','product_id','size_id','product_size_id',
        'product_name','unit_price','quantity','line_total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // 👇 yeh teen relations add karo
    public function product()
    {
        return $this->belongsTo(Product::class); // FK: product_id
    }

    public function sizeItem()
    {
        return $this->belongsTo(ProductSizeItem::class, 'size_id'); // FK: size_id
    }

    public function variant()
    {
        return $this->belongsTo(ProductSize::class, 'product_size_id'); // FK: product_size_id
    }
}
