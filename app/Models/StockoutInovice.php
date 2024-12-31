<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class StockoutInovice extends Model
{ 
    use HasFactory, HasApiTokens;
    protected $guarded=[''];
    protected $table = 'stockout_inovices';

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function receiver()
    {
        return $this->belongsTo(Receiver::class);
    }
    public function stockOutDetails()
    {
        return $this->hasMany(StockOutDetail::class, 'stockout_inovice_id');
    }
}
