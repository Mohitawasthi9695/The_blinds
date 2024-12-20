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
            $table->unsignedBigInteger('stock_invoice_details_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('width')->nullable();
            $table->string('length')->nullable();
            $table->string('unit')->nullable();
            $table->string('qty')->nullable(); 
            $table->boolean('status')->default(1);
            $table->timestamps();

            $table->foreign('stock_invoice_details_id')
                  ->references('id')
                  ->on('stock_invoice_details')
                  ->onDelete('cascade');

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
