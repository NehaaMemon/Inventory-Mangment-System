<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\WareHouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;


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
 try {
        $request->validate([
            'date' => ['required', 'date'],
            'customer_id' => ['required', 'exists:customers,id'],
            'warehouse_id' => ['required', 'exists:ware_houses,id'],
            'status' => ['required', Rule::in(['Return', 'Pending', 'Ordered'])],
            'discount' => ['nullable', 'numeric'],
            'shipping' => ['nullable', 'numeric'],
            'full_paid' => ['nullable','numeric'],
            'note' => ['nullable', 'string'],
            'grand_total' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'due_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        // Check if products array exists
        if (empty($request->products) || !is_array($request->products)) {
            return redirect()->back()
                ->withErrors(['products' => 'At least one product is required.'])
                ->withInput();
        }

        $request->validate([
            'products' => ['required', 'array', 'min:1'],
            'products.*.net_unit_cost' => ['required', 'numeric', 'min:0'],
            'products.*.quantity' => ['required', 'numeric', 'min:0.01'], // Allow decimals but must be > 0
            'products.*.discount' => ['nullable', 'numeric', 'min:0'],
            'products.*.subtotal' => ['required', 'numeric', 'min:0'],
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Sale Return Update Validation Failed', [
            'errors' => $e->errors(),
            'request_data' => $request->all(),
            'sale_return_id' => $id
        ]);
        return redirect()->back()
            ->withErrors($e->errors())
            ->withInput()
            ->with('error', 'Validation failed. Please check the form and try again.');
    }
    try {
        DB::beginTransaction();

        $saleReturn = SaleReturn::findOrFail($id);

        // =========================
        // 1. REVERT OLD STOCK
        // =========================
        $oldItems = SaleReturnItem::where('sale_return_id', $saleReturn->id)->get();

        foreach ($oldItems as $oldItem) {
            Product::where('id', $oldItem->product_id)
                ->decrement('product_qty', $oldItem->quantity);
        }

        // Delete old items
        SaleReturnItem::where('sale_return_id', $saleReturn->id)->delete();

        // =========================
        // 2. ADD NEW ITEMS + UPDATE STOCK
        // =========================
        foreach ($request->products as $product_id => $item) {
            $product = Product::findOrFail($product_id);

            // Ensure quantity is numeric
            $quantity = (float)$item['quantity'];
            $netUnitCost = (float)$item['net_unit_cost'];
            $discount = isset($item['discount']) ? (float)$item['discount'] : 0;
            $subtotal = isset($item['subtotal']) ? (float)$item['subtotal'] : (($netUnitCost * $quantity) - $discount);

            SaleReturnItem::create([
                'sale_return_id' => $saleReturn->id,
                'product_id' => $product_id,
                'net_unit_cost' => $netUnitCost,
                'stock' => $product->product_qty, // Current stock at time of return
                'quantity' => $quantity,
                'discount' => $discount,
                'subtotal' => $subtotal,
            ]);

            // Increment product stock (return means stock increases)
            $product->increment('product_qty', $quantity);
        }

        // =========================
        // 3. UPDATE SALE RETURN
        // =========================
        // Calculate Payment & Due Amount (Server-side reliability)
        $paidAmount = 0;
        if ($request->has('paid_amount') && $request->paid_amount !== null && $request->paid_amount !== '') {
            $paidAmount = (float)$request->paid_amount;
        }

        $fullPaid = 0;
        if ($request->has('full_paid') && $request->full_paid !== null && $request->full_paid !== '') {
            $fullPaid = (float)$request->full_paid;
        }

        $grandTotal = (float)$request->grand_total;
        $totalPaidSoFar = $paidAmount + $fullPaid;
        $dueAmount = $grandTotal - $totalPaidSoFar;

        $saleReturn->update([
            'date' => $request->date,
            'customer_id' => $request->customer_id,
            'warehouse_id' => $request->warehouse_id,
            'status' => $request->status,
            'discount' => $request->discount ?? 0,
            'shipping' => $request->shipping ?? 0,
            'note' => $request->note,
            'grand_total' => $grandTotal,
            'paid_amount' => $paidAmount,
            'full_paid' => $fullPaid,
            'due_amount' => max(0, $dueAmount),
        ]);

        DB::commit();

        $notify = [
            'message' => 'Sale Return Updated Successfully',
            'alert-type' => 'success'
        ];

        return redirect()
            ->route('sale-return.index')
            ->with($notify);

    } catch (\Throwable $e) {
        DB::rollBack();

        \Log::error('Sale Return Update Exception', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'sale_return_id' => $id,
            'request_data' => $request->all()
        ]);

        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Update failed: ' . $e->getMessage());
    }
}
//end method
    public function detail(string $id)
    {
        $saleReturn = SaleReturn::with('customer','saleReturnItems.product')->findOrFail($id);
        return view('admin.backend.sale-return.detail', compact('saleReturn'));
    }
    //end method
       public function invoice(string $id)
    {
        $saleReturn = SaleReturn::with('customer','saleReturnItems.product','warehouse')->findOrFail($id);
        $pdf = Pdf::loadView('admin.backend.sale-return.invoice', compact('saleReturn'));
        return $pdf->download('sale-return'. $saleReturn->id . '.pdf');
    }
    //end method

    public function destroy(string $id)
    {
        $saleReturn = SaleReturn::findOrFail($id);
        $saleReturnItems = SaleReturnItem::where('sale_return_id', $saleReturn->id)->get();

        // Revert stock
        foreach ($saleReturnItems as $item) {
            $product = Product::find($item->product_id);
            if($product){
                $product->decrement('product_qty',$item->quantity);
            }
        }

        // Delete sale return items
        SaleReturnItem::where('sale_return_id', $saleReturn->id)->delete();
        $saleReturn->delete();
        $notify = [
            'message' => 'Sale Return Deleted Successfully',
            'alert-type' => 'success'
        ];

        return redirect()->route('sale-return.index')->with($notify);

        return redirect()
            ->route('sale-return.index')
            ->with($notify);
    }

}
