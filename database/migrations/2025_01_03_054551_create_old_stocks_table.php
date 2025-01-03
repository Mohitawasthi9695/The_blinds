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
        Schema::create('old_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_code')->nullable();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('length', 15, 5)->nullable();
            $table->decimal('width', 15, 5)->nullable();
            $table->decimal('available_height', 15, 5)->nullable();
            $table->decimal('available_width', 15, 5)->nullable();
            $table->string('unit')->default('meter');
            $table->string('type')->nullable();
            $table->integer('qty')->nullable(); 
            $table->string('rack')->nullable(); 
            $table->string('Warehouse')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('old_stocks');
    }
};
