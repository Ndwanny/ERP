<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'name', 'slug', 'sku', 'description', 'price', 'cost_price',
        'production_minutes', 'quote_category', 'employee_eligible', 'model_list_price',
        'intl_usd_price', 'is_quote_enabled', 'compare_price', 'stock_quantity',
        'is_custom_order', 'is_published', 'images', 'variants', 'tags', 'vertical', 'featured',
    ];

    protected $casts = [
        'images' => 'array', 'variants' => 'array', 'tags' => 'array',
        'is_custom_order' => 'boolean', 'is_published' => 'boolean', 'featured' => 'boolean',
        'employee_eligible' => 'boolean', 'is_quote_enabled' => 'boolean',
        'price' => 'decimal:2', 'cost_price' => 'decimal:2',
        'model_list_price' => 'decimal:2', 'intl_usd_price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function orderItems()
    {
        return $this->hasMany(EcommerceOrderItem::class);
    }
}
