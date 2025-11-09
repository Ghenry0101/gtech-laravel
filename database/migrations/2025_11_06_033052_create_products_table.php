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
            $table->unsignedBigInteger('price');     
            $table->integer('stock')->default(0);  
            // data untuk pengiriman (dimensi barang)
            $table->integer('height')->default(0);   // tinggi (cm)
            $table->integer('length')->default(0);   // panjang (cm)
            $table->integer('width')->default(0);    // lebar (cm)
            $table->integer('weight')->default(0);   // berat (gram)   
            $table->string('image_product')->nullable();    
            $table->boolean('is_active')->default(true); 
            $table->enum('discount_type', ['percentage', 'amount'])->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->decimal('discount_amount')->nullable();

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
