<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('monthly_sales_temp', function (Blueprint $table) {
            $table->id();

            $table->integer('year');
            $table->integer('month');
            $table->decimal('total_sales', 15, 2);

            $table->integer('orders_count');

            $table->integer('products_sold');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_sales_temp');
    }
};
