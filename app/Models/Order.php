<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $fillable = [
        'user_id',
        'total_price',
        'discount',
        'status',
        'notes',
        'shipping_address',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
{
    return $this->belongsToMany(Product::class, 'order_products', 'order_id', 'product_id')
                ->withPivot('quantity', 'price_at_purchase');
}
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
