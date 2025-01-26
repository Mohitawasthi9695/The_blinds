<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class StockOutDetail extends Model
{
    use HasApiTokens, HasFactory;
    protected $guarded = [];

    protected $hidden = ['created_at', 'updated_at'];
    protected $table = 'stock_out_details';
    public function stockOutInvoice()
    {
        return $this->belongsTo(StockoutInovice::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
