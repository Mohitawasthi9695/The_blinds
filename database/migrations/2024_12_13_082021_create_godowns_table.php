<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('godowns', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no');
            $table->foreignId('stock_in_id')->constrained('stocks_ins')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->unsignedBigInteger('warehouse_supervisor_id');
            $table->unsignedBigInteger('godown_supervisor_id');
            $table->string('stock_code')->nullable();
            $table->date('date');
            $table->string('product_type')->nullable();
            $table->string('lot_no')->nullable();
            $table->decimal('get_width', 10, 5)->nullable();
            $table->decimal('get_length', 10, 5)->nullable();
            $table->decimal('available_height', 15, 5)->nullable();
            $table->decimal('available_width', 15, 5)->nullable();
            $table->integer('get_quantity')->nullable(); 
            $table->string('unit')->nullable();
            $table->string('type')->nullable();
            $table->string('waste_width')->nullable();
            $table->string('rack')->nullable();
            $table->integer('status')->default(0);
            $table->foreign('warehouse_supervisor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('godown_supervisor_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('godowns');
    }
};
