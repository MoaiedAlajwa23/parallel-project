<?php

namespace App\Console\Commands;

use App\Jobs\ProcessDailySalesChunkJob;
use App\Models\Order;
use Illuminate\Console\Command;

class GenerateDailySalesReport extends Command
{
    protected $signature = 'sales:monthly';

    public function handle(): void
    {
        Order::whereDate(
            'created_at',
            today()
        )
        ->select('id')
        ->chunkById(100, function ($orders) {

            ProcessDailySalesChunkJob::dispatch(
                $orders->pluck('id')->toArray()
            );
        });
    }
}
