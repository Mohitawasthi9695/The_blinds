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
  public function stockIns()
    {
        return $this->hasMany(StocksIn::class, 'product_id', 'id')->where('status', 1);
    }
}
