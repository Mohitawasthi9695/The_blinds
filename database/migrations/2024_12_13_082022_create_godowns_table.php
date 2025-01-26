<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('godowns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gate_pass_id')->constrained('gate_passes')->onDelete('cascade');
            $table->foreignId('stock_in_id')->constrained('stocks_ins')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('stock_code')->nullable();
            $table->string('product_type')->nullable();
            $table->string('lot_no')->nullable();
            $table->decimal('get_width', 10, 5)->nullable();
            $table->decimal('get_length', 10, 5)->nullable();
            $table->decimal('available_height', 15, 5)->nullable();
            $table->decimal('available_width', 15, 5)->nullable();
            $table->integer('get_quantity')->nullable();
            $table->string('unit')->nullable();
            $table->string('type')->nullable();
            $table->string('waste_width')->nullable();
            $table->string('rack')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });

        DB::unprepared('
        CREATE TRIGGER auto_increment_stock_code
        BEFORE INSERT ON godowns
        FOR EACH ROW
        BEGIN
            IF NEW.stock_code IS NULL THEN
                SET NEW.stock_code = (SELECT COALESCE(MAX(stock_code), 1) + 1 FROM godowns);
            END IF;
        END
    ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('godowns');
    }
};
