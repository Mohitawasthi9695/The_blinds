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
            $table->string('lot_no');
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('invoice_no')->nullable();
            $table->decimal('length', 15, 5)->nullable();
            $table->decimal('width', 15, 5)->nullable();
            $table->string('unit')->default('meter');
            $table->string('type')->nullable();
            $table->integer('qty')->nullable(); 
            $table->string('rack')->nullable(); 
            $table->boolean('status')->default(1);
            $table->timestamps();
            $table->foreign('invoice_id')
                  ->references('id')
                  ->on('stock_invoices')
                  ->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks_ins');
    }
};
