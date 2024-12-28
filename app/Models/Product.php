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
  public function stockAvaible()
    {
        return $this->hasMany(AvailableStock::class, 'product_id', 'id')->with('products')->where('status', 1);
    }
    public function AvaibleProducts()
    {
        return $this->whereHas('stockAvaible')->get();
    }
}
