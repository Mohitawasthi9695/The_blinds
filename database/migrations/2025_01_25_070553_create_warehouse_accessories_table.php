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
            $table->string('lot_no')->nullable();
            $table->string('length')->nullable();
            $table->string('length_unit')->nullable();
            $table->string('items')->nullable();
            $table->string('box_bundle')->nullable();
            $table->string('out_box_bundle')->nullable();
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
