<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlySalesReport extends Model
{
    protected $table = 'monthly_sales_reports';
    protected $fillable = [
        'year',
        'month',
        'total_sales',
        'orders_count',
        'products_sold',
        'average_order_value',
    ];
    
}
