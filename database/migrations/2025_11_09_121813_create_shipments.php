<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('courier_name')->nullable();
            $table->string('courier_service')->nullable();
            $table->string('biteship_order_id')->nullable();
            $table->string('tracking_id')->nullable();
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->enum('status', ['processing','shipped','delivered','failed'])->default('processing');
            $table->integer('estimation_days')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('shipments');
    }
};
