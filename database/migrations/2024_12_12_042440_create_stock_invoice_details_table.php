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
        Schema::create('stock_invoice_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_invoice_id')->constrained('stock_invoices')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('total_product')->nullable();
            $table->string('product_type')->nullable();
            $table->string('hsn_sac_code')->nullable();
            $table->decimal('width', 10, 5)->nullable();
            $table->decimal('height', 10, 5)->nullable();
            $table->decimal('quantity', 15, 5)->nullable(); 
            $table->string('unit')->nullable();
            $table->decimal('rate', 10, 5)->nullable();
            $table->decimal('amount', 15, 5)->nullable(); 
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_invoice_details');
    }
};
