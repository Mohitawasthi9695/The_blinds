<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('godown_vertical_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gate_pass_id')->constrained('gate_passes')->onDelete('cascade');
            $table->foreignId('stock_in_id')->constrained('stocks_ins')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('stock_code')->nullable();
            $table->date('date')->nullable();
            $table->string('lot_no')->nullable();
            $table->string('type')->nullable();
            $table->decimal('length', 12, 3)->nullable();
            $table->string('length_unit')->default('meter');
            $table->decimal('out_length', 12, 3)->nullable();
            $table->integer('pcs')->nullable();
            $table->integer('out_pcs')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('rack')->nullable();
            $table->integer('status')->default(0);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
        DB::unprepared('
        CREATE TRIGGER auto_increment_vertical_stock_code
        BEFORE INSERT ON godown_vertical_stocks
        FOR EACH ROW
        BEGIN
            DECLARE next_number INT;
            DECLARE next_code VARCHAR(10);
            SELECT COALESCE(MAX(CAST(SUBSTRING(stock_code, 3, 2) AS UNSIGNED)), 0) + 1 
            INTO next_number
            FROM godown_vertical_stocks;
            SET next_code = CONCAT("VT", LPAD(next_number, 2, "0"));
            IF NEW.stock_code IS NULL THEN
                SET NEW.stock_code = next_code;
            END IF;
        END');
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('godown_vertical_stocks');
    }
};
