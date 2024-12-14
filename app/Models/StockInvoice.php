<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class StockInvoice extends Model
{
    use HasFactory, HasApiTokens;
    protected $guarded = [];
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function receiver()
    {
        return $this->belongsTo(Receiver::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
    public function products()
    {
        return $this->hasMany(StockInvoiceDetail::class);
    }
}
