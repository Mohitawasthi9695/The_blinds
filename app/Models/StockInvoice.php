<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class StockInvoice extends Model
{
    use HasFactory, HasApiTokens;
    protected $guarded = [];

    protected $hidden = ['created_at', 'updated_at'];
    public function supplier()
    {
        return $this->belongsTo(People::class,'supplier_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function stock_in()
    {
        return $this->hasMany(StocksIn::class, 'invoice_id');
    }


}
