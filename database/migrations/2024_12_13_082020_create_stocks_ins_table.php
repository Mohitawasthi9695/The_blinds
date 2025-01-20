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
            $table->string('lot_no');
            $table->unsignedBigInteger('stock_code')->nullable()->unique();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->string('invoice_no')->nullable();
            $table->decimal('length', 15, 5)->nullable();
            $table->decimal('width', 15, 5)->nullable();
            $table->decimal('available_height', 15, 5)->nullable();
            $table->decimal('available_width', 15, 5)->nullable();
            $table->string('unit')->default('meter');
            $table->string('type')->nullable();
            $table->integer('qty')->nullable();
            $table->string('rack')->nullable();
            $table->string('warehouse')->nullable();
            $table->boolean('status')->default(1);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');            
            $table->foreign('invoice_id')->references('id')->on('stock_invoices')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->timestamps();
        });
        DB::unprepared('
        CREATE TRIGGER auto_increment_stock_code
        BEFORE INSERT ON stocks_ins
        FOR EACH ROW
        BEGIN
            IF NEW.stock_code IS NULL THEN
                SET NEW.stock_code = (SELECT COALESCE(MAX(stock_code), 1) + 1 FROM stocks_ins);
            END IF;
        END
    ');
    }
    public function down(): void
    {
        Schema::dropIfExists('stocks_ins');
    }
};
