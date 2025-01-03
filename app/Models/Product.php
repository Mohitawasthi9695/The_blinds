<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Product extends Model
{
  use HasFactory,HasApiTokens;

  protected $guarded = [];
  public function products()
  {
      return $this->hasMany(StockInvoiceDetail::class);
  }
public function stockAvailable()
{   
    return $this->hasMany(StocksIn::class, 'product_id')->where(function($query) {
        $query->where('qty', '>=', 1);
    });
}
public function stockOutDetails()
{
    return $this->hasMany(StockOutDetail::class, 'product_id');
}
}
