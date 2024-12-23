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
        Schema::create('receivers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('code', 10)->unique();
            $table->string('gst_no', 100)->nullable()->unique();
            $table->string('cin_no', 100)->nullable();
            $table->string('pan_no', 10)->nullable();
            $table->string('msme_no', 100)->nullable()->unique();
            $table->string('reg_address', )->nullable();
            $table->string('work_address',)->nullable();
            $table->string('area', 50)->nullable();
            $table->string('tel_no', 20)->nullable();
            $table->string('email', 40)->nullable()->unique();
            $table->string('owner_mobile', 10)->nullable(); // Mobile Number
            $table->string('logo')->nullable();
            $table->boolean('status')->default(1); // Status (1 = Active, 0 = Inactive)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receivers');
    }
};
