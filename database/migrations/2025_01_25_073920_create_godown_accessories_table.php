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
        Schema::create('godown_accessories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gate_pass_id')->nullable()->constrained('gate_passes')->onDelete('cascade');
            $table->foreignId('product_accessory_id')->constrained('product_accessories')->onDelete('cascade');
            $table->foreignId('warehouse_accessory_id')->nullable()->constrained('warehouse_accessories')->onDelete('cascade');
            $table->string('stock_code')->nullable();
            $table->date('date')->nullable();
            $table->string('lot_no')->nullable();
            $table->string('type')->nullable();
            $table->string('length')->nullable();
            $table->string('length_unit')->nullable();
            $table->string('items')->nullable();
            $table->string('box_bundle')->nullable();
            $table->string('box_bundle_unit')->nullable();
            $table->string('quantity')->nullable();
            $table->string('out_quantity')->default(0)->nullable();
            $table->string('transfer')->nullable();
            $table->string('remark')->nullable();
            $table->string('rack')->nullable();
            $table->unsignedBigInteger('row_id')->nullable();
            $table->boolean('status')->default(0);
            $table->timestamps();
        });
        DB::unprepared('
        CREATE TRIGGER auto_godown_accessory_stock_code
        BEFORE INSERT ON godown_accessories
        FOR EACH ROW
        BEGIN
            DECLARE next_number BIGINT;
            DECLARE next_code VARCHAR(15);
            SELECT COALESCE(MAX(CAST(SUBSTRING(stock_code, 3) AS UNSIGNED)), 0) + 1 
            INTO next_number
            FROM godown_accessories;
            SET next_code = CONCAT("GA", LPAD(next_number, 8, "0"));
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
        Schema::dropIfExists('godown_accessories');
    }
};
