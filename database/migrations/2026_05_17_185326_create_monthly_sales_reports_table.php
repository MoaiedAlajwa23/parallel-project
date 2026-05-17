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
        Schema::create('monthly_sales_reports', function (Blueprint $table) {

            $table->id();
            $table->integer('year');
            $table->integer('month');
            $table->decimal('total_sales', 15, 2)->default(0);
            $table->integer('orders_count')->default(0);
            $table->integer('products_sold')->default(0);
            $table->decimal('average_order_value', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_sales_reports');
    }
};
