<?php

namespace App\Models;

use App\Models\ProductSize;
use App\Models\ProductImage;
use App\Models\ProductSizeItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'desc'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
    public function sizes()
    {
        return $this->hasMany(ProductSize::class);
    }
    // This will return the first size price, but you can adjust it as needed
    public function getPriceAttribute()
    {
        return $this->sizes->first()->price ?? 0;
    }
    public function ProductsizeItem()
    {
        return $this->hasMany(ProductSizeItem::class);
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_products');
    }
}
