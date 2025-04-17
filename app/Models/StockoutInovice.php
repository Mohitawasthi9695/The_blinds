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

    protected $hidden = ['created_at', 'updated_at'];
    public function customer()
    {
        return $this->belongsTo(People::class,'customer_id');
    }
    public function company()
    {
        return $this->belongsTo(People::class,'company_id');
    }

    public function stockOutDetails()
    {
        return $this->hasMany(StockOutDetail::class, 'stockout_inovice_id');
    }
    
}
