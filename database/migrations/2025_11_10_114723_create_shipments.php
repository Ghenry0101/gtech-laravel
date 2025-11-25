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
        Schema::create('shipments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->string('courier_name', 50)->nullable();
            $table->string('courier_service', 50)->nullable();
            $table->string('tracking_id', 100)->nullable();
            $table->string('waybill_id', 150)->nullable();
            $table->string('biteship_order_id', 100)->nullable();
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->string('status')->default('processing');
            $table->integer('estimation_days')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('label_url', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
