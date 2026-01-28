<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
        protected $fillable = [
        'date',
        'from_warehouse_id',
        'to_warehouse_id',
        'discount',
        'shipping',
        'status',
        'note',
        'grand_total',

    ];

    public function fromwarehouse(){
        return $this->belongsTo(WareHouse::class , 'from_warehouse_id');
    }

       public function towarehouse(){
        return $this->belongsTo(WareHouse::class , 'to_warehouse_id');
       }

     public function transferItems(){
        return $this->hasMany(TransferItem::class , 'transfer_id');
    }
}
