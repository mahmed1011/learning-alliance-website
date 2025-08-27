<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'parent_id'];

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    public function getSlugAttribute()
    {
        return Str::slug($this->name);
    }

    // public function products()
    // {
    //     return $this->hasMany(Product::class, 'category_id');
    // }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_products');
    }


    // Root categories (no parent)
    public function scopeRoots($q)
    {
        return $q->whereNull('parent_id');
    }

    /**
     * Collect this category + children + grandchildren IDs.
     * (3-level menu need ke liye enough)
     */
    public function descendantIds(): array
    {
        $ids = [$this->id];

        // level-1 (direct children)
        $level1 = $this->children()->pluck('id')->all();
        $ids = array_merge($ids, $level1);

        // level-2 (grandchildren)
        if (!empty($level1)) {
            $level2 = self::whereIn('parent_id', $level1)->pluck('id')->all();
            $ids = array_merge($ids, $level2);
        }

        return array_values(array_unique($ids));
    }
}
