<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Models\Product;

class PrimeCacheCommand extends Command
{
    protected $signature = 'cache:prime';
    protected $description = 'Prime the distributed cache with best sellers using pure Eloquent';

    public function handle()
    {
        $this->info('Priming the cache for Best Selling Products...');

        $bestsellers = Product::query()
            ->where('is_active', true)
  
            ->whereHas('orderProducts', function ($query) {
                $query->whereHas('order', function ($orderQuery) {
                    $orderQuery->where('status', 'completed');
                });
            })
            ->withSum(['orderProducts as total_sold' => function ($query) {
                $query->whereHas('order', function ($orderQuery) {
                    $orderQuery->where('status', 'completed');
                });
            }], 'quantity')

            ->orderByDesc('total_sold')
            ->take(10)
            ->get();

        Cache::tags(['products_list'])->put('store:bestsellers', $bestsellers, 86400);
        
        $this->info('Cache primed successfully with ' . $bestsellers->count() . ' best sellers!');
    }
}