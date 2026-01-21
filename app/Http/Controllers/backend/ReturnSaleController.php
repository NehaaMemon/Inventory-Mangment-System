<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\WareHouse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReturnSaleController extends Controller
{
    public function index()
    {
        $saleReturn = SaleReturn::orderBy('id', 'desc')->get();
        return view('admin.backend.sale-return.index', compact('saleReturn'));
    }
    //end method

    public function create()
    {
        $customers = Customer::all();
        $warehouses = WareHouse::all();
        return view('admin.backend.sale-return.create', compact('customers', 'warehouses'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'date' => ['required', 'date'],
                'customer_id' => ['required', 'exists:customers,id'],
                'warehouse_id' => ['required', 'exists:ware_houses,id'],
                'status' => ['required', Rule::in(['Return', 'Pending', 'Ordered'])],
                'discount' => ['nullable', 'numeric'],
                'shipping' => ['nullable', 'numeric'],
                'note' => ['nullable', 'string'],
                'grand_total' => ['required', 'decimal:2', 'min:0'],
                'paid_amount' => ['nullable', 'integer', 'min:0'],
                'due_amount' => ['nullable', 'decimal:2', 'min:0']
            ]);

            $request->validate([
                'products' => ['required', 'array', 'min:1'],
                'products.*.id' => ['required', 'exists:products,id'],
                'products.*.cost' => ['required', 'numeric', 'min:0'],
                'products.*.quantity' => ['required', 'integer', 'min:1'],
                'products.*.discount' => ['nullable', 'numeric'],
            ]);
            // dd($request->all());
            $grand_total = 0;

            $saleReturn = new SaleReturn();
            $saleReturn->date = $request->date;
            $saleReturn->customer_id = $request->customer_id;
            $saleReturn->warehouse_id = $request->warehouse_id;
            $saleReturn->status = $request->status;
            $saleReturn->discount = $request->discount ?? 0;
            $saleReturn->shipping = $request->shipping ?? 0;
            $saleReturn->note = $request->note;
            $saleReturn->grand_total = $request->grand_total;
            $saleReturn->paid_amount = $request->paid_amount;
            $saleReturn->due_amount = $request->due_amount;
            $saleReturn->save();

            foreach ($request->products as $item) {
                $product = Product::findOrFail($item['id']);
                $netUnitCost = $item['cost'] ?? $product->price;
                $subTotal = ($netUnitCost * $item['quantity'])  - ($item['discount'] ?? 0);
                $grand_total += $subTotal;

                // CREATE SALE RETURN ITEM
                $saleReturnItem = new SaleReturnItem();
                $saleReturnItem->sale_return_id = $saleReturn->id;
                $saleReturnItem->product_id = $product->id;
                $saleReturnItem->net_unit_cost = $netUnitCost;
                $saleReturnItem->stock = $product->product_qty + $item['quantity'];
                $saleReturnItem->quantity = $item['quantity'];
                $saleReturnItem->discount = $item['discount'] ?? 0;
                $saleReturnItem->subtotal = $subTotal;
                $saleReturnItem->save();

                $product->increment('product_qty', $item['quantity']);
            }

            $saleReturn->update([
                'grand_total' => $grand_total + ($request->shipping ?? 0) - ($request->discount ?? 0)
            ]);


            $notify = array(
                'message' => 'Sale Return Added Successfully',
                'alert-type' => 'success'

            );
            return redirect()->route('sale-return.index')->with($notify);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
    //end method
    public function edit(string $id)
    {
        $saleReturn = SaleReturn::with('saleReturnItems.product')->findOrFail($id);
        $customers = Customer::all();
        $warehouses = WareHouse::all();
        return view('admin.backend.sale-return.edit', compact('saleReturn', 'customers', 'warehouses'));
    }

    public function update(Request $request, string $id)
    {

        $request->validate([
            'date' => ['required', 'date'],
            'customer_id' => ['required', 'exists:customers,id'],
            'warehouse_id' => ['required', 'exists:ware_houses,id'],
            'status' => ['required', Rule::in(['Return', 'Pending', 'Ordered'])],
            'discount' => ['nullable', 'numeric'],
            'shipping' => ['nullable', 'numeric'],
            'note' => ['nullable', 'string'],
            'grand_total' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'due_amount' => ['nullable', 'numeric', 'min:0'],
            'full_paid' => ['nullable', 'numeric', 'min:0'],

        ]);
        $request->validate([
            'products' => ['required', 'array'],
            'products.*.net_unit_cost' => ['required', 'numeric', 'min:0'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
            'products.*.discount' => ['nullable', 'numeric'],
        ]);
        // dd($request->all());
        try {
            $saleReturn = SaleReturn::findOrFail($id);
            $saleReturn->date = $request->date;
            $saleReturn->customer_id = $request->customer_id;
            $saleReturn->warehouse_id = $request->warehouse_id;
            $saleReturn->status = $request->status;
            $saleReturn->discount = $request->discount ?? 0;
            $saleReturn->shipping = $request->shipping ?? 0;
            $saleReturn->note = $request->note;
            $saleReturn->grand_total = $request->grand_total;
            $saleReturn->paid_amount = $request->paid_amount;
            $saleReturn->due_amount = $request->due_amount ?? 0;
            $saleReturn->full_paid = $request->full_paid ?? 0;

            $oldItems = SaleReturnItem::where('sale_return_id',$saleReturn->id)->get();

            foreach($oldItems as $oldItem){
                $product = Product::find($oldItem->product_id);
                if($product){
                    $product->decrement('product_qty',$oldItem->quantity);
                }
            }
            SaleReturnItem::where('sale_return_id',$saleReturn->id)->delete();
            $grand_total = 0;

            foreach($request->products as $product_id => $item){
                $productModal = Product::findOrFail($product_id);
                $netUnitCost = $item['net_unit_cost'] ?? $productModal->price;
                $quantity = $item['quantity'];
                $discount = $item['discount'] ?? 0;
                $subTotal = ($netUnitCost * $quantity) - $discount;
                $grand_total += $subTotal;

                  SaleReturnItem::create([
                        'sale_return_id' => $saleReturn->id,
                        'product_id' => $product_id,
                        'net_unit_cost' => $netUnitCost,
                        'stock' => $productModal->product_qty + $quantity,
                        'quantity' => $quantity,
                        'discount' => $discount,
                        'subtotal' => $subTotal
                    ]);

                $productModal->increment('product_qty',$quantity);
            }
                $saleReturn->update([
                    'grand_total' => $grand_total + ($request->shipping ?? 0) - ($request->discount ?? 0)
                ]);
                $saleReturn->save();
                    $notify = array(
                    'message' => 'Sale Return Updated Successfully',
                    'alert-type' => 'success'
                );
                return redirect()->route('sale-return.index')->with($notify);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
}
