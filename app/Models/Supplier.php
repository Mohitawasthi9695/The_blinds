<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Supplier extends Model
{
    use HasFactory, HasApiTokens;
    protected $guarded = [];
    public function stockInvoices()
    {
        return $this->hasMany(StockInvoice::class) ->select('id', 'invoice_no', 'supplier_id','total_amount','date'); ;
    }
    public function RecentInvoice()
    {
        return $this->hasMany(StockInvoice::class) ->select('id', 'invoice_no', 'supplier_id','total_amount','date')->orderBy('created_at', 'desc')->limit(1);
    }

}
