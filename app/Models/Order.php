<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'cart_key',
        'campus',
        'parent_name',
        'student_name',
        'class',
        'section',
        'phone',
        'email',
        'subtotal',
        'total',
        'status',
        'payment_status',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'total'    => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
