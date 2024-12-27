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
        Schema::create('stock_available', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_ins_id')->constrained('stocks_ins')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('length', 15, 5)->nullable();
            $table->decimal('width', 15, 5)->nullable();
            $table->string('unit')->default('meter');
            $table->decimal('area',15,5)->nullable();
            $table->decimal('area_sq_ft',15,5)->nullable();
            $table->string('type')->nullable();
            $table->integer('qty')->nullable(); 
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
        Schema::dropIfExists('stock_available');
    }
};
