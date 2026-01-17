<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
         protected $fillable = [
        'date',
        'supplier_id',
        'warehouse_id',
        'discount',
        'shipping',
        'status',
        'note',
        'grand_total', // <-- add this
    ];

    public function warehouse(){
        return $this->belongsTo(Warehouse::class);
    }
    public function supplier(){
        return $this->belongsTo(Supplier::class);
    }
     public function purchaseItems(){
        return $this->hasMany(PurchaseReturnItem::class,'purchase_return_id');
    }
   public function product(){
        return $this->belongsTo(Product::class);
    }
}
