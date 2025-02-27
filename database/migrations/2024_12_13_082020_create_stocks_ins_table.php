<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stocks_ins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->foreign('invoice_id')->references('id')->on('stock_invoices')->onDelete('cascade');
            $table->foreignId('product_category_id')->constrained('product_categories')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('invoice_no')->nullable();
            $table->string('stock_code')->nullable();
            $table->string('lot_no')->nullable();
            $table->date('date')->nullable();
            $table->decimal('length', 15, 3)->nullable();
            $table->string('length_unit')->default('meter')->nullable();
            $table->decimal('width', 15, 3)->nullable();
            $table->string('width_unit')->default('meter')->nullable();
            $table->integer('quantity')->nullable(); 
            $table->integer('out_quantity')->nullable(); 
            $table->integer('pcs')->nullable();
            $table->string('rack')->nullable();
            $table->string('remark')->nullable();
            $table->boolean('status')->default(1);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');            
            $table->timestamps();
        });

        DB::unprepared('
        CREATE TRIGGER auto_stock_code
        BEFORE INSERT ON stocks_ins
        FOR EACH ROW
        BEGIN
            DECLARE next_number INT;
            DECLARE next_code VARCHAR(10);
            SELECT COALESCE(MAX(CAST(SUBSTRING(stock_code, 3, 2) AS UNSIGNED)), 0) + 1 
            INTO next_number
            FROM stocks_ins;
            SET next_code = CONCAT("ST", LPAD(next_number, 2, "0"));
            IF NEW.stock_code IS NULL THEN
                SET NEW.stock_code = next_code;
            END IF;
        END');
    
    }
    public function down(): void
    {
        Schema::dropIfExists('stocks_ins');
    }
};
