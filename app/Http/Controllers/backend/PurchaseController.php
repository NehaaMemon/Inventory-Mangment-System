<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseController extends Controller
{
    // List all purchases
    public function index()
    {
        $purchases = Purchase::latest()->get();
        return view('admin.backend.purchase.index', compact('purchases'));
    }

    // Show form to create a purchase
    public function create()
    {
        $warehouses = Warehouse::all();
        $suppliers = Supplier::all();
        return view('admin.backend.purchase.create', compact('warehouses', 'suppliers'));
    }




    // Product search for AJAX
    public function purchaseProductSearch(Request $request)
    {
        $query = $request->query('query');
        $warehouse_id = $request->query('warehouse_id');

        $products = Product::query()
            ->when($query, function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('code', 'LIKE', "%{$query}%");
            })
            ->when($warehouse_id, function($q) use ($warehouse_id) {
                // Assuming products have warehouse_id column
                $q->where('warehouse_id', $warehouse_id);
            })
            ->select('id', 'name', 'code', 'price', 'product_qty', 'stock_alert')

            ->limit(10)
            ->get();

        return response()->json($products);
    }

    public function store(Request $request)
{
    try{
    // VALIDATION -----------------------------------------
    $request->validate([
        'date' => ['required', 'date'],
        'supplier_id' => ['required', 'exists:suppliers,id'],
        'warehouse_id' => ['required', 'exists:ware_houses,id'],
        'status' => ['required', Rule::in(['Pending','Received','Ordered'])],
        'discount' => ['nullable','numeric'],
        'shipping' => ['nullable','numeric'],
        'note' =>['nullable','string'],
        'grand_total' => ['required','decimal:2','min:0'],

    ]);

   $request->validate([
    'products.*.id' => ['required','exists:products,id'],
    'products.*.cost' => ['required','numeric','min:0'],
    'products.*.quantity' => ['required','integer','min:1'],
    'products.*.discount' => ['nullable','numeric'],
]);

// dd($request->all());


    // CREATE PURCHASE -------------------------------------
    $purchase = new Purchase();
    $purchase->date = $request->date;
    $purchase->supplier_id = $request->supplier_id;
    $purchase->warehouse_id = $request->warehouse_id;
    $purchase->discount = $request->discount ?? 0;
    $purchase->shipping = $request->shipping ?? 0;
    $purchase->status = $request->status;
    $purchase->note = $request->note;
    $purchase->grand_total = 0;
    $purchase->save();  // VERY IMPORTANT TO GENERATE ID



    // CALCULATE GRAND TOTAL --------------------------------
    $grandtotal = 0;


    foreach ($request->products as $item) {
    $product = Product::findOrFail($item['id']); // JS me product_id ka naam 'id' hai
    $netUnitCost = $item['cost'] ?? $product->price;
    $subtotal = ($netUnitCost * $item['quantity']) - ($item['discount'] ?? 0);
    $grandtotal += $subtotal;

    // CREATE PURCHASE ITEM
    $purchaseitem = new PurchaseItem();
    $purchaseitem->purchase_id = $purchase->id;
    $purchaseitem->product_id = $product->id;
    $purchaseitem->net_unit_cost = $netUnitCost;
    $purchaseitem->stock = $product->product_qty + $item['quantity'];
    $purchaseitem->quantity = $item['quantity'];
    $purchaseitem->discount = $item['discount'] ?? 0;
    $purchaseitem->total_price = $subtotal;
    $purchaseitem->save();

    // UPDATE PRODUCT STOCK
    $product->increment('product_qty', $item['quantity']);
}


$purchase->update([
    'grand_total' => $grandtotal + ($request->shipping ?? 0) - ($request->discount ?? 0),
]);


      $notify = array(
                'message' => 'Purchase Added Successfully',
                'alert-type' => 'success'

            );
            return redirect()->route('purchase.index')->with($notify);
        }
        catch (\Illuminate\Validation\ValidationException $e) {
        // Log full errors
        Log::error($e->getMessage() . "\n" . $e->getTraceAsString());

        // Return JSON for Ajax debugging
        return response()->json(['errors' => $e->errors()], 422);
    }
}

public function edit(string $id){
     $purchasedata = Purchase::with('purchaseItems.product')->findOrFail($id);
     $warehouses = Warehouse::all();
     $suppliers = Supplier::all();
     return view('admin.backend.purchase.edit', compact('purchasedata','warehouses', 'suppliers'));
}


public function update(Request $request, string $id)
{
    // 1. VALIDATION (Try block se bahar rakhein)
    $request->validate([
        'date' => ['required', 'date'],
        'supplier_id' => ['required', 'exists:suppliers,id'],
        'warehouse_id' => ['required', 'exists:ware_houses,id'],
        'status' => ['required', Rule::in(['Pending', 'Received', 'Ordered'])],
        'grand_total' => ['required', 'numeric', 'min:0'],
        'products' => ['required', 'array'], // Products array check
    ]);

    // 2. START TRANSACTION
    DB::beginTransaction();

    try {
        // --- STEP A: Update Purchase Main Table ---
        $purchase = Purchase::findOrFail($id);
        $purchase->date = $request->date;
        $purchase->supplier_id = $request->supplier_id;
        $purchase->warehouse_id = $request->warehouse_id;
        $purchase->discount = $request->discount ?? 0;
        $purchase->shipping = $request->shipping ?? 0;
        $purchase->status = $request->status;
        $purchase->note = $request->note;
        $purchase->grand_total = $request->grand_total;
        $purchase->save();

        // --- STEP B: Reverse Old Stock (Decrement Old Qty) ---
        $oldPurchaseItems = PurchaseItem::where('purchase_id', $purchase->id)->get();

        foreach ($oldPurchaseItems as $olditem) {
            $product = Product::find($olditem->product_id);
            if ($product) {
                // Purani quantity stock se hatao
                $product->decrement('product_qty', $olditem->quantity);
            }
        }

        // --- STEP C: Delete Old Items ---
        PurchaseItem::where('purchase_id', $purchase->id)->delete();

        // --- STEP D: Insert New Items & Update Stock ---
        foreach ($request->products as $productId => $productData) {

            // Product find karo
            $product = Product::findOrFail($productId);

            // Values clean karo
            $quantity = $productData['quantity']; // (int) mat lagaye agar decimal allowed hai
            $netUnitCost = $productData['net_unit_cost'];
            $discount = $productData['discount'] ?? 0;

            // Demo request se subtotal le raha hai, par calculate karna safe hai:
            // Agar aap request se lena chahte hain: $totalPrice = $productData['subtotal'];
            $totalPrice = ($quantity * $netUnitCost) - $discount;

            // Purchase Item Create
            $purchaseitem = new PurchaseItem();
            $purchaseitem->purchase_id = $purchase->id;
            $purchaseitem->product_id = $productId;
            $purchaseitem->net_unit_cost = $netUnitCost;

            // NOTE: Yahan 'stock' column mein wo value jayegi jo abhi product ki hai (minus hone ke baad)
            // Demo mein ye form se aa raha tha ($productData['stock']).
            // Better hai hum current DB stock lein:
            $purchaseitem->stock = $product->product_qty;

            $purchaseitem->quantity = $quantity;
            $purchaseitem->discount = $discount;
            $purchaseitem->total_price = $totalPrice; // Aapke DB column ka naam 'total_price' hai
            $purchaseitem->save();

            // Stock Increment (Naya Stock Add karo)
            $product->increment('product_qty', $quantity);
        }

        // 3. COMMIT TRANSACTION (Sab kuch sahi hone par save karo)
        DB::commit();

        $notify = array(
            'message' => 'Purchase Updated Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('purchase.index')->with($notify);

    } catch (\Exception $e) {
        // 4. ROLLBACK (Agar koi error aaya to sab purani halat mein wapis)
        DB::rollBack();

        Log::error($e->getMessage());

        // Error dekhne ke liye:
        return redirect()->back()->with('error', 'Update Failed: ' . $e->getMessage());
    }
}

function show(string $id){
    $purchase = Purchase::with(['supplier','purchaseItems.product'])->find($id);
    return view('admin.backend.purchase.show',compact('purchase'));
}

function invoice(string $id)  {
    $purchase = Purchase::with(['supplier','warehouse','purchaseItems.product'])->find($id);
    $pdf = Pdf::loadView('admin.backend.purchase.invoice_pdf',compact('purchase'));
     return $pdf->download('purchase'.$id.'.pdf');

}

function destroy(string $id)  {
    try{
        $purchase = Purchase::findOrFail($id);
        $purchaseItem = PurchaseItem::where('purchase_id',$id)->get();
        foreach ($purchaseItem as $item) {
            $product = Product::find($item->product_id);
            if($product){
                $product->decrement('product_qty',$item->quantity);
            }
        }
        PurchaseItem::where('purchase_id',$id)->delete();
        $purchase->delete();
         $notify = array(
            'message' => 'Purchase Deleted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('purchase.index')->with($notify);


    }
    catch(\Exception $e){

    }

}


}
