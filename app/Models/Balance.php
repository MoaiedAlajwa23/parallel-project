<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'discount'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAmount()
    {
        return $this->amount;
    }
    public function getDiscount()
    {
        return $this->discount;
    }
}
