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
        Schema::create('godowns', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->foreignId('stock_in_id')->constrained('stocks_ins')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->unsignedBigInteger('warehouse_supervisor_id');
            $table->unsignedBigInteger('godown_supervisor_id');
            $table->string('stock_code')->nullable()->unique();
            $table->date('date');
            $table->string('product_type')->nullable();
            $table->string('hsn_sac_code')->nullable();
            $table->decimal('get_width', 10, 5)->nullable();
            $table->decimal('get_length', 10, 5)->nullable();
            $table->decimal('get_quantity', 15, 5)->nullable();
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
