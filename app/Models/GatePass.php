<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class GatePass extends Model
{
    use HasFactory, HasApiTokens;
    protected $guarded = [''];
    protected $hidden = ['created_at', 'updated_at'];

    public function warehouse_supervisors()
    {
        return $this->belongsTo(User::class, 'warehouse_supervisor_id');
    }
    public function godown_supervisors()
    {
        return $this->belongsTo(User::class, 'godown_supervisor_id');
    }
    public function godown_roller_stock()
    {
        return $this->hasMany(GodownRollerStock::class);
    }
    public function godown_wooden_stock()
    {
        return $this->hasMany(GodownWoodenStock::class);
    }
    public function godown_vertical_stock()
    {
        return $this->hasMany(GodownVerticalStock::class);
    }
    public function godown_honeycomb_stock()
    {
        return $this->hasMany(GodownHoneyCombStock::class);
    }
    public function godown_accessories()
    {
        return $this->hasMany(GodownAccessory::class);
    }
}
