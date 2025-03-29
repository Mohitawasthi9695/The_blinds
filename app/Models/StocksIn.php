<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class StocksIn extends Model
{
    use HasFactory, HasApiTokens;
    protected $guarded = [''];

    protected $hidden = ['created_at', 'updated_at'];
    public function stockInvoiceDetails()
    {
        return $this->belongsTo(StockInvoiceDetail::class, 'stock_invoice_details_id');
    }

    public function stockInvoice()
    {
        return $this->belongsTo(StockInvoice::class, 'invoice_id');
    }
    public function products()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function supplier()
    {
        return $this->hasOneThrough(
            People::class,        // Final Model (Target)
            StockInvoice::class,  // Intermediate Model
            'id',                 // Foreign key on StockInvoice (Linking to StockIn)
            'id',                 // Foreign key on People (Linking to StockInvoice)
            'invoice_id',         // Local key on StockIn
            'supplier_id'         // Local key on StockInvoice
        );
        
    }
    public function godown_roller_stock()
    {
        return $this->hasMany(GodownRollerStock::class, 'stock_in_id');
    }

}
