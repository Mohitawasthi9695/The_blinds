<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Receiver extends Model
{
    use HasFactory, HasApiTokens;
    protected $guarded = [];
    public function stockInvoices()
    {
        return $this->hasMany(StockInvoice::class);
    }
    public function stockOutInvoices()
    {
        return $this->hasMany(StockoutInovice::class);
    }

}
