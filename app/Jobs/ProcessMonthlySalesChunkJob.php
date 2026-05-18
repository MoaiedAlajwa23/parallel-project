<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessMonthlySalesChunkJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 3;

    public $timeout = 120;

    public function __construct(
        public array $orderIds,
        public int $year,
        public int $month
    ) {}

    public function handle(): void
    {
        
        $orders = Order::with('products')
            ->whereIn('id', $this->orderIds)
            ->get();

        $totalSales = 0;

        $productsSold = 0;

        foreach ($orders as $order) {

            $totalSales += $order->total_price;

            foreach ($order->products as $product) {

                $productsSold += $product->pivot->quantity;
            }
        }

        DB::table('monthly_sales_temp')->insert([
            'year' => $this->year,
            'month' => $this->month,
            'total_sales' => $totalSales,
            'orders_count' => $orders->count(),
            'products_sold' => $productsSold,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
