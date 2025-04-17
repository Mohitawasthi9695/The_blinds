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
        Schema::create('cut_accessories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('godown_accessory_id')->constrained('godown_accessories')->onDelete('cascade');
            $table->string('stock_code')->nullable();
            $table->date('date')->nullable();
            $table->string('lot_no')->nullable();
            $table->string('type')->nullable();
            $table->string('length')->nullable();            
            $table->string('length_unit')->nullable();
            $table->string('quantity')->nullable();
            $table->string('out_quantity')->default(0)->nullable();
            $table->string('remark')->nullable();
            $table->string('rack')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
        DB::unprepared('
        CREATE TRIGGER auto_godown_accessory_cut_stock_code
        BEFORE INSERT ON cut_accessories
        FOR EACH ROW
        BEGIN
            DECLARE next_number BIGINT;
            DECLARE next_code VARCHAR(255);
            SELECT COALESCE(MAX(CAST(SUBSTRING(stock_code, 3) AS UNSIGNED)), 0) + 1 
            INTO next_number
            FROM cut_accessories;
            SET next_code = CONCAT("CA",next_number);
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
        Schema::dropIfExists('cut_accessories');
    }
};
