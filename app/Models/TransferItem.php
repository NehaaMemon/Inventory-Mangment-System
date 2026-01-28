<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferItem extends Model
{
     protected $fillable = [
        'transfer_id',
        'product_id',
        'net_unit_cost',
        'stock',
        'quantity',
        'discount',
        'subtotal'
    ];

    public function product(){
        return $this->belongsTo(Product::class , 'product_id');
    }
}
