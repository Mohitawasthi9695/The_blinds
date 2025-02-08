<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stocks_ins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->foreign('invoice_id')->references('id')->on('stock_invoices')->onDelete('cascade');
            $table->foreignId('product_category_id')->constrained('product_categories')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('invoice_no')->nullable();
            $table->string('lot_no')->nullable();
            $table->decimal('length', 15, 3)->nullable();
            $table->string('length_unit')->nullable();
            $table->decimal('width', 15, 3)->nullable();
            $table->string('width_unit')->nullable();
            $table->decimal('thickness', 15, 3)->nullable();
            $table->string('thickness_unit')->nullable();
            $table->decimal('area', 15, 3)->nullable();
            $table->string('area_unit')->nullable();
            $table->string('type')->nullable();
            $table->integer('pcs')->nullable();
            $table->integer('total_quantity')->nullable();
            $table->integer('out_quantity')->nullable();
            $table->string('rack')->nullable();
            $table->string('warehouse')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('stocks_ins');
    }
};
