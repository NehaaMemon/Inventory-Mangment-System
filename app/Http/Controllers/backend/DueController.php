<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Http\Request;

class DueController extends Controller
{
  public function dueSale()  {
    $sale = Sale::with(['warehouse','customer'])
        ->select('id','warehouse_id','customer_id','due_amount')
        ->where('due_amount','>',0)
        ->get();
    return view('admin.backend.due.sale_due',compact('sale'));
  }
  //end method

  public function dueSaleReturn()  {
    $saleReturn = SaleReturn::with(['warehouse','customer'])
            ->select('id','warehouse_id','customer_id','due_amount')
            ->where('due_amount','>',0)
            ->get();

    return view('admin.backend.due.sale-return_due',compact('saleReturn'));

  }
}
