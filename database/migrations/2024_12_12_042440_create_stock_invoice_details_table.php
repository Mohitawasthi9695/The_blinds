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
            $table->string('total_product');
            $table->string('product_type');
            $table->string('hsn_sac_code')->nullable();
            $table->float('quantity')->nullable();
            $table->string('length')->nullable();
            $table->string('width')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('rate', 10, 2)->nullable();
            $table->decimal('amount', 10, 2)->nullable();
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
