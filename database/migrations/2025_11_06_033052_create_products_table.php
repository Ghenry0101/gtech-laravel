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
        Schema::create('products', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name', 100);
            $table->string('sku', 50)->unique();
            $table->string('slug', 200)->unique();       
            $table->text('description')->nullable();      
            $table->unsignedInteger('stock')->default(0);
            $table->decimal('price', 12, 2);
            $table->decimal('discount_percent', 5, 2)->nullable(); 
            $table->decimal('discount_price', 12, 2)->nullable();
            $table->timestamp('discount_start')->nullable();
            $table->timestamp('discount_end')->nullable();
            $table->integer('height')->default(0);  
            $table->integer('length')->default(0); 
            $table->integer('width')->default(0);
            $table->integer('weight')->default(0); 
            $table->string('product_image', 255)->nullable();    
            $table->boolean('is_active')->default(true); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
