<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            $table->string('stock_code')->nullable();
            $table->string('lot_no')->nullable();
            $table->date('date')->nullable();
            $table->string('length')->nullable();
            $table->string('length_unit')->nullable();
            $table->string('items')->nullable();
            $table->string('box_bundle');
            $table->string('out_box_bundle')->nullable();
            $table->string('remark')->nullable();
            $table->string('rack')->nullable();
            $table->string('quantity')->nullable();
            $table->string('out_quantity')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
        DB::unprepared('
        CREATE TRIGGER auto_accessory_stock_code
        BEFORE INSERT ON warehouse_accessories
        FOR EACH ROW
        BEGIN
            DECLARE next_number INT;
            DECLARE next_code VARCHAR(10);
            SELECT COALESCE(MAX(CAST(SUBSTRING(stock_code, 3, 2) AS UNSIGNED)), 0) + 1 
            INTO next_number
            FROM warehouse_accessories;
            SET next_code = CONCAT("WA", LPAD(next_number, 2, "0"));
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
        Schema::dropIfExists('warehouse_accessories');
    }
};
