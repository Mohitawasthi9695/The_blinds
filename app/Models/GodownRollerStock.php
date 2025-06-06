<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;


class GodownRollerStock extends Model
{
    use HasFactory, HasApiTokens;
    protected $guarded = [''];
    protected $hidden = ['created_at', 'updated_at'];
    public function products()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function stocks()
    {
        return $this->belongsTo(StocksIn::class, 'stock_in_id');
    }
    public function gatepass()
    {
        return $this->belongsTo(GatePass::class, 'gate_pass_id');
    }
    public function cutstock()
    {
        return $this->HasMany(CutStock::class, 'godown_roller_stock_id');
    }
    public function godown_transfer()
    {
        return $this->hasMany(GodownRollerStock::class, 'row_id', 'id');
    }
}
