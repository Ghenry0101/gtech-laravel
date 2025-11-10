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
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            $table->string('name');             
            $table->string('slug')->unique();       
            $table->text('description')->nullable();      
            $table->integer('stock')->default(0);
            $table->decimal('price', 12, 2);
            $table->decimal('discount_percent', 5, 2)->nullable(); // contoh: 10.00 berarti diskon 10%
            $table->decimal('discount_price', 12, 2)->nullable();  // harga setelah diskon (opsional)
            $table->timestamp('discount_start')->nullable();
            $table->timestamp('discount_end')->nullable();
            // data untuk pengiriman (dimensi barang)
            $table->integer('height')->default(0);   // tinggi (cm)
            $table->integer('length')->default(0);   // panjang (cm)
            $table->integer('width')->default(0);    // lebar (cm)
            $table->integer('weight')->default(0);   // berat (gram)   
            $table->string('product_image')->nullable();    
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
