<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;


class GodownAccessory extends Model
{
    use HasFactory, HasApiTokens;
    protected $guarded = [''];
    protected $hidden = ['create_at','updated_at'];

    public function gatepass()
    {
        return $this->belongsTo(GatePass::class, 'gate_pass_id', 'id');
    }
    public function accessory()
    {
        return $this->belongsTo(ProductAccessory::class, 'product_accessory_id', 'id');
    }
    public function warehouse_accessory()
    {
        return $this->belongsTo(WarehouseAccessory::class, 'warehouse_accessory_id', 'id');
    }
    public function accessory_transfer()
    {
        return $this->HasMany(GodownAccessory::class, 'row_id', 'id');
    }
    public function accessory_transfer_from()
    {
        return $this->belongsTo(GodownAccessory::class, 'row_id', 'id');
    }
    public function cutstocks()
    {
        return $this->HasMany(CutAccessory::class, 'godown_accessory_id');
    }
}
