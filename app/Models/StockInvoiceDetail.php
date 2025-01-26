<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class StockInvoiceDetail extends Model
{
    use HasFactory, HasApiTokens;
    protected $guarded = [];

    protected $hidden = ['created_at', 'updated_at'];
    public function invoice()
    {
        return $this->belongsTo(StockInvoice::class, 'stock_invoice_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

}
