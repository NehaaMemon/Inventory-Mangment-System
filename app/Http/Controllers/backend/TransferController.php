<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Transfer;
use App\Models\WareHouse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TransferController extends Controller
{
    public function index()  {
        $transfer = Transfer::with(['transferItems.product'])->orderby('id','desc')
                ->get();
        return view('admin.backend.transfer.index',compact('transfer'));

    }
    //end method//

    public function create() : View {
        $warehouses = WareHouse::all();
        return view('admin.backend.transfer.create',compact('warehouses'));
    }
    //end method//
    public function store(Request $request) {
        $request->validate([
            'date' => ['required','date'],
            'from_warehouse' => ['required', 'exists:ware_houses,id'],
            'to_warehouse' => ['required', 'exists:ware_houses,id'],
            'status' => ['required', Rule::in(['Transfer', 'Pending', 'Ordered'])],
            'discount' => ['nullable', 'numeric'],
            'shipping' => ['nullable', 'numeric'],
            'note' => ['nullable', 'string'],
            'grand_total' => ['required', 'decimal:2', 'min:0'],
        ]);
        // dd($request->all());

    }
}
