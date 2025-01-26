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
        Schema::create('gate_passes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_supervisor_id');
            $table->unsignedBigInteger('godown_supervisor_id');
            $table->foreign('warehouse_supervisor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('godown_supervisor_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('gate_pass_no')->unique();
            $table->string('gate_pass_date')->now();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gate_passes');
    }
};
