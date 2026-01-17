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
        $table->string('name');
        $table->string('code')->unique();
        $table->string('image');

        // Foreign keys properly
        $table->foreignId('category_id')->constrained('product_categories')->onDelete('cascade');
        $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
        $table->foreignId('warehouse_id')->constrained('ware_houses')->onDelete('cascade');
        $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
        $table->decimal('price', 8, 2);
        $table->decimal('discount_price', 8, 2)->nullable();
        $table->integer('stock_alert')->default(0);
        $table->text('note')->nullable();
        $table->integer('product_qty')->default(0);
        $table->string('status')->default('pending');
        $table->string('active_status')->default('active');
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
