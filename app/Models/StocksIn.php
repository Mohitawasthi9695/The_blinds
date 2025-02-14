<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class StocksIn extends Model
{
    use HasFactory,HasApiTokens;
    protected $guarded=[''];

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
        return $this->belongsTo(People::class, 'supplier_id');
    }


}
