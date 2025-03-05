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
        Schema::create('stockout_accessory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stockout_inovice_id')->constrained('stockout_inovices')->onDelete('cascade');
            $table->foreignId('godown_accessory_id')->constrained('godown_accessories')->onDelete('cascade');
            $table->foreignId('product_accessory_id')->constrained('product_accessories')->onDelete('cascade');
            $table->string('stock_code')->nullable();
            $table->string('hsn_sac_code')->nullable();
            $table->date('date')->nullable();
            $table->string('lot_no')->nullable();
            $table->decimal('length',13,2)->nullable();
            $table->string('length_unit')->nullable();
            $table->integer('items')->nullable();
            $table->integer('out_quantity')->nullable();
            $table->string('box_bundle')->nullable();
            $table->decimal('gst', 10, 5)->nullable();
            $table->decimal('rate', 10, 5)->nullable();
            $table->decimal('amount', 15, 5)->nullable();
            $table->string('rack')->nullable();
            $table->string('remark')->nullable();
            $table->boolean('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stockout_accessory');
    }
};
