<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItem extends Model
{
     public function product(){
        return $this->belongsTo(Product::class);
    }
    public function purchasereturn(){
        return $this->belongsTo(PurchaseReturn::class,'purchase_return_id');
    }
}
