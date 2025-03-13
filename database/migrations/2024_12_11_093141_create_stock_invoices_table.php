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
        Schema::create('stock_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->unsignedBigInteger('supplier_id');
            $table->date('date');
            $table->string('place_of_supply')->nullable();
            $table->string('vehicle_no')->nullable();
            $table->string('station')->nullable();
            $table->string('ewaybill')->nullable();
            $table->boolean('reverse_charge')->default(false);
            $table->string('gr_rr')->nullable();
            $table->string('transport')->nullable();
            $table->decimal('transport_gst', 5, 2)->nullable();
            $table->string('agent')->nullable();
            $table->string('warehouse')->nullable();
            $table->string('irn')->nullable();
            $table->string('ack_no')->nullable();
            $table->date('ack_date')->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('cgst_percentage', 5, 2)->nullable();
            $table->decimal('sgst_percentage', 5, 2)->nullable();
            $table->decimal('igst_percentage', 5, 2)->nullable();
            $table->string('qr_code')->nullable();
            $table->boolean('status')->default(1);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('peoples')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_invoices');
    }
};
