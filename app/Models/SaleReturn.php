<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class SaleReturn extends Model
{
     protected $fillable = [
        'date',
        'customer_id',
        'warehouse_id',
        'discount',
        'shipping',
        'status',
        'note',
        'grand_total',
        'paid_amount',
        'due_amount',
        'full_paid'
    ];

    public function warehouse(){
        return $this->belongsTo(WareHouse::class);
    }

       public function customer(){
        return $this->belongsTo(Customer::class);
       }

     public function saleReturnItems(){
        return $this->hasMany(SaleReturnItem::class);
    }

}
