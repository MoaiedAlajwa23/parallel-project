<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $table = 'carts';
    protected $fillable = [
        'id',
        'user_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cartProducts()
    {
        return $this->hasMany(CartProduct::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'cart_products')->withPivot('quantity');
    }
    public function isEmpty()
    {
        return $this->products()->count() === 0;
    }
}
