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
        Schema::create('payments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->string('midtrans_order_id', 100)->nullable();
            $table->string('transaction_id', 100)->nullable();
            $table->string('payment_type', 50)->nullable();
            $table->string('va_number', 50)->nullable();
            $table->string('bank', 50)->nullable();
            $table->decimal('gross_amount', 12, 2)->nullable();
            $table->enum('payment_status', ['pending','settlement','cancel','expire'])->default('pending');
            $table->string('fraud_status', 50)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
