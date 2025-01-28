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
        Schema::create('warehouse_accessories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_accessory_id')->constrained('product_accessories')->onDelete('cascade');
            $table->string('length')->nullable();
            $table->string('unit')->nullable();
            $table->string('items')->nullable();
            $table->string('box')->nullable();
            $table->string('quantity')->nullable();
            $table->string('out_quantity')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_accessories');
    }
};
