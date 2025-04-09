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
        Schema::create('cut_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('godown_roller_stock_id')->constrained('godown_roller_stocks')->onDelete('cascade');
            $table->foreignId('stockout_inovice_id')->constrained('stockout_inovices')->onDelete('cascade');
            $table->string('sub_stock_code')->nullable();
            $table->decimal('width', 12, 3)->nullable();
            $table->string('width_unit')->nullable();
            $table->decimal('length', 12, 3)->nullable();
            $table->decimal('out_length', 12, 3)->nullable();
            $table->string('length_unit')->nullable();
            $table->string('remark')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
        DB::unprepared('
        CREATE TRIGGER auto_increment_cut_stocks_sub_stock_code
        BEFORE INSERT ON cut_stocks
        FOR EACH ROW
        BEGIN
            DECLARE next_number BIGINT;
            DECLARE next_code VARCHAR(255);
            SELECT COALESCE(MAX(CAST(SUBSTRING(sub_stock_code, 3) AS UNSIGNED)), 0) + 1 
            INTO next_number
            FROM cut_stocks;
            SET next_code = CONCAT("CS",next_number);
            IF NEW.sub_stock_code IS NULL THEN
                SET NEW.sub_stock_code = next_code;
            END IF;
        END');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cut_stocks');
    }
};
