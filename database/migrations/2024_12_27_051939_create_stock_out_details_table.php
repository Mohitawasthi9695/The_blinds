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
        Schema::create('stock_out_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stockout_inovice_id')->constrained('stockout_inovices')->onDelete('cascade');
            $table->foreignId('stock_in_id')->constrained('stocks_ins')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('product_type')->nullable();
            $table->string('hsn_sac_code')->nullable();
            $table->decimal('out_width', 10, 5)->nullable();
            $table->decimal('out_length', 10, 5)->nullable();
            $table->decimal('out_quantity', 15, 5)->nullable();
            $table->string('unit')->nullable();
            $table->string('type')->nullable();
            $table->string('waste_width')->nullable();
            $table->decimal('rate', 10, 5)->nullable();
            $table->decimal('amount', 15, 5)->nullable();
            $table->string('rack')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_out_details');
    }
};
