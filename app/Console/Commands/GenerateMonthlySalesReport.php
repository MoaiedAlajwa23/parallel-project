<?php

namespace App\Console\Commands;

use App\Jobs\FinalizeMonthlySalesReportJob;
use App\Jobs\ProcessDailySalesChunkJob;
use App\Jobs\ProcessMonthlySalesChunkJob;
use App\Models\Order;
use Illuminate\Console\Command;

class GenerateMonthlySalesReport extends Command
{
    protected $signature = 'sales:monthly';

    public function handle(): void
    {
        $year = now()->year;

        $month = now()->month;

        Order::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->select('id')
            ->chunkById(100, function ($orders)
                use ($year, $month) {

                ProcessMonthlySalesChunkJob::dispatch(
                    $orders->pluck('id')->toArray(),
                    $year,
                    $month
                );
            });

        FinalizeMonthlySalesReportJob::dispatch(
            $year,
            $month
        )->delay(now()->addMinutes(1));
    }
}  

