<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'products';
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'stock',
        'is_active',
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }
    
    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function carts()
    {
        return $this->belongsToMany(Cart::class, 'cart_products')->withPivot('quantity');
    }
    public function getIsActive(){
        return $this->is_active;
    }
    public function getStock(){
        return $this->stock;
    }
}
