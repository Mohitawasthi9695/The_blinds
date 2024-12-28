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
        Schema::create('stockout_inovices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->date('date');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('receiver_id');
            $table->string('place_of_supply')->nullable();
            $table->string('vehicle_no')->nullable();
            $table->string('station')->nullable();
            $table->string('ewaybill')->nullable();
            $table->boolean('reverse_charge')->default(false);
            $table->string('gr_rr')->nullable();
            $table->string('transport')->nullable();
            $table->string('irn')->nullable();
            $table->string('ack_no')->nullable();
            $table->date('ack_date')->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('cgst_percentage', 5, 2)->nullable();
            $table->decimal('sgst_percentage', 5, 2)->nullable();
            $table->string('payment_mode')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('payment_date')->nullable();
            $table->string('payment_Bank')->nullable();
            $table->string('payment_account_no')->nullable();
            $table->string('payment_ref_no')->nullable();
            $table->decimal('payment_amount',10,5)->nullable();
            $table->string('payment_remarks')->nullable();
            $table->string('qr_code')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
            $table->foreign('receiver_id')->references('id')->on('receivers')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stockout_inovices');
    }
};
