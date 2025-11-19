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
        Schema::create('orders', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('recipient_name', 100);
            $table->string('phone', 20);
            $table->string('tracking_code', 100)->nullable();
            $table->string('full_address', 255);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('subtotal_amount', 12, 2)->default(0);
            $table->enum('order_status', ['pending','processing','shipped','completed','canceled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('order_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
