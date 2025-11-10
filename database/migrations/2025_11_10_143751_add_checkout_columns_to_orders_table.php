<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_number', 40)->nullable()->after('address_id');
            $table->string('payment_method')->nullable()->after('order_status');
            $table->timestamp('paid_at')->nullable()->after('payment_method');
        });

        DB::table('orders')
            ->whereNull('order_number')
            ->orderBy('id')
            ->chunkById(100, function ($orders): void {
                foreach ($orders as $order) {
                    DB::table('orders')
                        ->where('id', $order->id)
                        ->update([
                            'order_number' => sprintf('GTECH-%08d', $order->id),
                        ]);
                }
            });

        Schema::table('orders', function (Blueprint $table) {
            $table->unique('order_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique('orders_order_number_unique');
            $table->dropColumn(['order_number', 'payment_method', 'paid_at']);
        });
    }
};
