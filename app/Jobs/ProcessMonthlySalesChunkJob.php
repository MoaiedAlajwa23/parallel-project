<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class ProcessDailySalesChunkJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $orderIds
    ) {}

    public function handle(): void
    {
        $orders = Order::whereIn('id', $this->orderIds)->get();

        $totalSales = $orders->sum('total_price');

        $ordersCount = $orders->count();

        $productsSold = 0;

        foreach ($orders as $order) {

            $productsSold += $order->products()->count();
        }

        DB::table('daily_sales_temp')->insert([
            'total_sales' => $totalSales,
            'orders_count' => $ordersCount,
            'products_sold' => $productsSold,
        ]);
    }
}
