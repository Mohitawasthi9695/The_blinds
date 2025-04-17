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
        return $this->belongsTo(StockoutInovice::class,'stockout_inovice_id');
    }
    public function Godown()
    {
        return $this->belongsTo(GodownRollerStock::class,'godown_id','id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function accessoryoutstock()
    {
        return $this->HasMany(StockoutAccessory::class,'stockout_details_id');
    }
    
}
