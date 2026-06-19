<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\ProductObserver;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([ProductObserver::class])]
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
        'views',
        'version',
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
    public function getViews(){
        return $this->views;
    }
    public function getVersion()
    {
        return $this->version;
    }
    

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_products')->withPivot('quantity','price_at_purchase');
    }
}
