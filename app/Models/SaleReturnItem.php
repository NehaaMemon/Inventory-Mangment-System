<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturnItem extends Model
{
   protected $fillable = [
        'sale_return_id',
        'product_id',
        'net_unit_cost',
        'stock',
        'quantity',
        'discount',
        'subtotal'
    ];

    public function product(){
        return $this->belongsTo(Product::class);
    }
    //   public function saleReturn(){
    //     return $this->belongsTo(SaleReturn::class);
    // }
}
