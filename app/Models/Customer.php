<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Model
{
    use HasFactory, HasApiTokens;
    protected $guarded = [];
   
    public function stockOutInvoices()
    {
        return $this->hasMany(StockoutInovice::class);
    }
}
