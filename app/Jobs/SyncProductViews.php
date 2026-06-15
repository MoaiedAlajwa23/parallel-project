<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncProductViews implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $productId;
    public $viewsCount;

    public function __construct($productId, $viewsCount)
    {
        $this->productId = $productId;
        $this->viewsCount = $viewsCount;
    }

    public function handle(): void
    {

        $product = Product::find($this->productId);

        if ($product) {
            $product->views = $this->viewsCount;
            $product->saveQuietly();
        }
    }
}