<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Supplier;
use App\Models\WareHouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;


class PurchaseReturnController extends Controller
{
    function returnPurchaseIndex() : View
    {
        $purchases = PurchaseReturn::orderBy('id','desc')->get();
        return view('admin.backend.purchase-return.index', compact('purchases'));

    }
    function returnPurchaseCreate() : View
    {
        $suppliers = Supplier::all();
        $warehouses = WareHouse::all();
        return view('admin.backend.purchase-return.create', compact('suppliers', 'warehouses'));
    }

    function returnPurchaseStore(Request $request)  {
        try {
        $request->validate([
            'date' =>[ 'required','date'],
            'warehouse_id' => ['required','exists:ware_houses,id'],
            'supplier_id' => ['required','exists:suppliers,id'],
            'status' => ['required', Rule::in(['Return','Pending','Ordered'])],
            'discount' => ['nullable','numeric'],
            'shipping' => ['nullable','numeric'],
            'note' => ['nullable','string'],
            'grand_total' => ['required','decimal:2','min:0']
        ]);

        $request->validate([
            'products.*.id' => ['required','exists:products,id'],
            'products.*.quantity' => ['required','numeric','min:1'],
            'products.*.cost' => ['required','numeric','min:0'],
            'products.*.discount' => ['nullable','numeric','min:0']
        ]);

        $returnpurchase = new PurchaseReturn();
        $returnpurchase->date = $request->date;
        $returnpurchase->warehouse_id = $request->warehouse_id;
        $returnpurchase->supplier_id = $request->supplier_id;
        $returnpurchase->status = $request->status;
        $returnpurchase->discount = $request->discount ?? 0;
        $returnpurchase->shipping = $request->shipping ?? 0;
        $returnpurchase->note = $request->note;
        $returnpurchase->grand_total = 0;
        $returnpurchase->save();
          $grandtotal = 0;


    foreach ($request->products as $item) {
    $product = Product::findOrFail($item['id']); // JS me product_id ka naam 'id' hai
    $netUnitCost = $item['cost'] ?? $product->price;
    $subtotal = ($netUnitCost * $item['quantity']) - ($item['discount'] ?? 0);
    $grandtotal += $subtotal;

    // CREATE PURCHASE ITEM
    $purchaseitem = new PurchaseReturnItem();
    $purchaseitem->purchase_return_id = $returnpurchase->id;
    $purchaseitem->product_id = $product->id;
    $purchaseitem->net_unit_cost = $netUnitCost;
    $purchaseitem->stock = $product->product_qty + $item['quantity'];
    $purchaseitem->quantity = $item['quantity'];
    $purchaseitem->discount = $item['discount'] ?? 0;
    $purchaseitem->total_price = $subtotal;
    $purchaseitem->save();

    // UPDATE PRODUCT STOCK
    $product->decrement('product_qty', $item['quantity']);
}


$returnpurchase->update([
    'grand_total' => $grandtotal + ($request->shipping ?? 0) - ($request->discount ?? 0),
]);


      $notify = array(
                'message' => 'Purchase Return created successfully',
                'alert-type' => 'success'

            );
            return redirect()->route('return-purchase.index')->with($notify);
        }
        catch (\Illuminate\Validation\ValidationException $e) {
        // Log full errors
        Log::error($e->getMessage() . "\n" . $e->getTraceAsString());

        // Return JSON for Ajax debugging
        return response()->json(['errors' => $e->errors()], 422);
    }
}

function returnPurchaseDetail(string $id) : View
{
    $purchase = PurchaseReturn::with(['warehouse','supplier','product'])->findOrFail($id);
    return view('admin.backend.purchase-return.detail', compact('purchase'));
}

function returnPurchaseInvoice(string $id)
{
    $purchase = PurchaseReturn::with(['warehouse','supplier','product'])->findOrFail($id);
    $pdf = Pdf::loadView('admin.backend.purchase.invoice_pdf',compact('purchase'));
     return $pdf->download('purchase'.$id.'.pdf');
}

function returnPurchaseEdit(string $id)
{
    $purchasedata = PurchaseReturn::with(['warehouse','supplier','product'])->findOrFail($id);
    $warehouses = WareHouse::all();
    $suppliers = Supplier::all();
    return view('admin.backend.purchase-return.edit', compact('purchasedata','warehouses','suppliers'));
}

function returnPurchaseUpdate(Request $request, string $id)
{
    $request->validate([
        'date' => ['required','date'],
        'warehouse_id' => ['required','exists:ware_houses,id'],
        'supplier_id' => ['required','exists:suppliers,id'],
        'status' => ['required', Rule::in(['Return','Pending','Ordered'])],
    ]);

    try {
        DB::beginTransaction();

        $purchase = PurchaseReturn::findOrFail($id);
        $purchase->date = $request->date;
        $purchase->supplier_id = $request->supplier_id;
        $purchase->warehouse_id = $request->warehouse_id;
        $purchase->discount = $request->discount ?? 0;
        $purchase->shipping = $request->shipping ?? 0;
        $purchase->status = $request->status;
        $purchase->note = $request->note;

        // Undo previous adjustments before applying new ones
        $oldItems = PurchaseReturnItem::where('purchase_return_id', $purchase->id)->get();
        foreach ($oldItems as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->increment('product_qty', $item->quantity);
            }
        }
        PurchaseReturnItem::where('purchase_return_id', $purchase->id)->delete();

        $grandTotal = 0;

        // Apply new items
        foreach ($request->products as $productId => $productData) {
            $product = Product::findOrFail($productId);
            $quantity = $productData['quantity'];
            $netUnitCost = $productData['net_unit_cost'];
            $discount = $productData['discount'] ?? 0;

            $totalPrice = ($quantity * $netUnitCost) - $discount;
            $grandTotal += $totalPrice;

            $purchaseitem = new PurchaseReturnItem();
            $purchaseitem->purchase_return_id = $purchase->id;
            $purchaseitem->product_id = $productId;
            $purchaseitem->net_unit_cost = $netUnitCost;
            $purchaseitem->stock = $product->product_qty;
            $purchaseitem->quantity = $quantity;
            $purchaseitem->discount = $discount;
            $purchaseitem->total_price = $totalPrice;
            $purchaseitem->save();

            // Returning items reduces available stock
            $product->decrement('product_qty', $quantity);
        }

        $purchase->grand_total = $grandTotal + $purchase->shipping - $purchase->discount;
        $purchase->save();

        DB::commit();
        $notify = array(
            'message' => 'Return Purchase Updated Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('return-purchase.index')->with($notify);
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error($e->getMessage(), ['exception' => $e]);
        return redirect()->back()->with('error', 'Update Failed: ' . $e->getMessage());
    }
}
function returnPurchaseDestroy(string $id)  {
    try{
        $purchase = PurchaseReturn::findOrFail($id);
        $purchaseItem = PurchaseReturnItem::where('purchase_return_id',$id)->get();
        foreach ($purchaseItem as $item) {
            $product = Product::find($item->product_id);
            if($product){
                $product->increment('product_qty',$item->quantity);
            }
        }
        PurchaseReturnItem::where('purchase_return_id',$id)->delete();
        $purchase->delete();
         $notify = array(
            'message' => 'Return Purchase Deleted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('return-purchase.index')->with($notify);
    }
    catch(\Exception $e){

    }
}
}
