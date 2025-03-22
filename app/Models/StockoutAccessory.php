<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class StockoutAccessory extends Model
{
    use HasApiTokens, HasFactory;
    protected $guarded = [];

    protected $hidden = ['created_at', 'updated_at'];
    protected $table = 'stockout_accessory';
    public function stockOutInvoice()
    {
        return $this->belongsTo(StockoutInovice::class,'stockout_inovice_id');
    }
    public function stockOutDetails()
    {
        return $this->belongsTo(StockoutInovice::class,'stockout_details_id');
    }
    public function accessory()
    {
        return $this->belongsTo(ProductAccessory::class,'product_accessory_id');
    }
}
