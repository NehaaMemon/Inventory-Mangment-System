<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\WareHouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SaleController extends Controller
{
    function index() {
        $sales =Sale::latest()->get();
        return view('admin.backend.sale.index',compact('sales'));
    }
        function create() {
         $warehouses = WareHouse::all();
         $customers = Customer::all();
        return view('admin.backend.sale.create',compact('warehouses','customers'));
    }
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
    //End Method

    function store(Request $request) {
        // Handle the store logic here
 try{
         $request->validate([
        'date' => ['required', 'date'],
        'customer_id' => ['required', 'exists:customers,id'],
        'warehouse_id' => ['required', 'exists:ware_houses,id'],
        'status' => ['required', Rule::in(['Sale','Pending','Ordered'])],
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

 $grandtotal = 0;

    // CREATE SALE -------------------------------------
    $sale = new Sale();
    $sale->date = $request->date;
    $sale->customer_id = $request->customer_id;
    $sale->warehouse_id = $request->warehouse_id;
    $sale->discount = $request->discount ?? 0;
    $sale->shipping = $request->shipping ?? 0;
    $sale->status = $request->status;
    $sale->note = $request->note;
    $sale->paid_amount = $request->paid_amount;
    $sale->due_amount = $request->due_amount;
    $sale->full_paid = $request->full_paid;
    $sale->grand_total = 0;
    $sale->save();




    foreach ($request->products as $item) {
    $product = Product::findOrFail($item['id']);
    $netUnitCost = $item['cost'] ?? $product->price;
    $subtotal = ($netUnitCost * $item['quantity']) - ($item['discount'] ?? 0);
    $grandtotal += $subtotal;

    // CREATE SALE ITEM
    $saleitem = new SaleItem();
    $saleitem->sale_id = $sale->id;
    $saleitem->product_id = $product->id;
    $saleitem->net_unit_cost = $netUnitCost;
    $saleitem->stock = $product->product_qty + $item['quantity'];
    $saleitem->quantity = $item['quantity'];
    $saleitem->discount = $item['discount'] ?? 0;
    $saleitem->subtotal = $subtotal;
    $saleitem->save();
    // UPDATE PRODUCT STOCK
    $product->decrement('product_qty', $item['quantity']);
}


$sale->update([
    'grand_total' => $grandtotal + ($request->shipping ?? 0) - ($request->discount ?? 0),
]);


      $notify = array(
                'message' => 'Sale Added Successfully',
                'alert-type' => 'success'

            );
            return redirect()->route('sale.index')->with($notify);
        }
        catch (\Illuminate\Validation\ValidationException $e) {
        // Log full errors
        Log::error($e->getMessage() . "\n" . $e->getTraceAsString());

        // Return JSON for Ajax debugging
        return response()->json(['errors' => $e->errors()], 422);
    }

}
    //End Method

    function edit($id) {
        $sale = Sale::with('saleItems.product')->findOrFail($id);
         $warehouses = WareHouse::all();
         $customers = Customer::all();
        return view('admin.backend.sale.edit',compact('sale','warehouses','customers'));
    }

    public function update(Request $request, string $id)  {
        $request->validate([
            'date' => ['required', 'date'],
            'customer_id' => ['required', 'exists:customers,id'],
            'warehouse_id' => ['required', 'exists:ware_houses,id'],
            'status' => ['required', Rule::in(['Sale','Pending','Ordered'])],
            'discount' => ['nullable','numeric'],
            'shipping' => ['nullable','numeric'],
            'note' =>['nullable','string'],
        ]);

        $request->validate([
            'products' => ['required', 'array'],
            'products.*.net_unit_cost' => ['required','numeric','min:0'],
            'products.*.quantity' => ['required','integer','min:1'],
            'products.*.discount' => ['nullable','numeric'],
        ]);

    try {
        DB::beginTransaction();

        $sale = Sale::findOrFail($id);
        $sale->date = $request->date;
        $sale->customer_id = $request->customer_id;
        $sale->warehouse_id = $request->warehouse_id;
        $sale->discount = $request->discount ?? 0;
        $sale->shipping = $request->shipping ?? 0;
        $sale->status = $request->status;
        $sale->note = $request->note;
        $sale->paid_amount = $request->paid_amount ?? 0;
        $sale->due_amount = $request->due_amount ?? 0;
        $sale->full_paid = $request->full_paid ?? 0;

        // Restore old stock (purane sale items ki quantity wapas add karo)
        $oldItems = SaleItem::where('sale_id', $sale->id)->get();
        foreach ($oldItems as $oldItem) {
            $product = Product::find($oldItem->product_id);
            if ($product) {
                // Purani sale ki quantity stock mein wapas add karo
                $product->increment('product_qty', $oldItem->quantity);
            }
        }

        // Delete old items
        SaleItem::where('sale_id', $sale->id)->delete();

        $grandTotal = 0;

        // Insert new items and update stock
        foreach ($request->products as $product_id => $product) {
            $productModal = Product::findOrFail($product_id);
            $netUnitCost = $product['net_unit_cost'] ?? $productModal->price;
            $quantity = $product['quantity'];
            $discount = $product['discount'] ?? 0;
            $subtotal = ($netUnitCost * $quantity) - $discount;
            $grandTotal += $subtotal;

            // Create sale item
            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $product_id,
                'net_unit_cost' => $netUnitCost,
                'stock' => $productModal->product_qty,
                'quantity' => $quantity,
                'discount' => $discount,
                'subtotal' => $subtotal
            ]);

            // Sale se stock kam hota hai
            $productModal->decrement('product_qty', $quantity);
        }

        // Update grand total
        $sale->grand_total = $grandTotal + ($request->shipping ?? 0) - ($request->discount ?? 0);
        $sale->save();

        DB::commit();

        $notify = array(
            'message' => 'Sale Updated Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('sale.index')->with($notify);

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error($e->getMessage(), ['exception' => $e]);
        return redirect()->back()->with('error', 'Update Failed: ' . $e->getMessage());
    }
    }

    public function Delete(string $id)  {
        $sale = Sale::findOrFail($id);
        $saleItem = SaleItem::where('sale_id',$id)->get();

        foreach($saleItem as $Item){
            $product = Product::find($Item->product_id);
            if($product){
                $product::increment('product_qty',$Item->quantity);
            }
        }
        SaleItem::where('sale_id',$id)->delete();
        $sale->delete();
          $notify = array(
            'message' => 'Sale Deleted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('sale.index')->with($notify);
    }
    //end method//

   public function Details(string $id)  {
    $sale = Sale::with(['customer','saleItems.product'])->find($id);
    return view('admin.backend.sale.details',compact('sale'));

    }
     //end method//

     public function Invoice(string $id)  {
        $sale = Sale::with(['customer','saleItems','warehouse'])->findOrFail($id);
        $pdf = Pdf::loadView('admin.backend.sale.invoice',compact('sale'));
        return $pdf->download('sale'.$id.'.pdf');
    }
}
