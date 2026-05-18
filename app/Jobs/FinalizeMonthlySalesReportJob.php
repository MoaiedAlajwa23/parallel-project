<?php

namespace App\Jobs;

use App\Models\MonthlySalesReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class FinalizeMonthlySalesReportJob implements ShouldQueue
{
    use Queueable,InteractsWithQueue, SerializesModels;

    public function __construct(
        public int $year,
        public int $month
    ) {}

    public function handle(): void
    {
        $data = DB::table('monthly_sales_temp')
            ->where('year', $this->year)
            ->where('month', $this->month)
            ->get();

        $totalSales = $data->sum('total_sales');

        $ordersCount = $data->sum('orders_count');

        $productsSold = $data->sum('products_sold');

        $averageOrderValue = $ordersCount > 0
            ? $totalSales / $ordersCount
            : 0;

        MonthlySalesReport::updateOrCreate(
            [
                'year' => $this->year,
                'month' => $this->month,
            ],
            [
                'total_sales' => $totalSales,
                'orders_count' => $ordersCount,
                'products_sold' => $productsSold,
                'average_order_value' => $averageOrderValue,
            ]
        );

        DB::table('monthly_sales_temp')
            ->where('year', $this->year)
            ->where('month', $this->month)
            ->delete();
        
    }
}