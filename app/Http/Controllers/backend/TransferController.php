<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\WareHouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;


class TransferController extends Controller
{
    public function index()
    {
        $transfer = Transfer::with(['transferItems.product'])->orderby('id', 'desc')
            ->get();
        return view('admin.backend.transfer.index', compact('transfer'));
    }
    //end method//

    public function create(): View
    {
        $warehouses = WareHouse::all();
        return view('admin.backend.transfer.create', compact('warehouses'));
    }
    //end method//
    public function store(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'from_warehouse_id' => ['required', 'exists:ware_houses,id'],
            'to_warehouse_id' => ['required', 'exists:ware_houses,id'],
            'status' => ['required', Rule::in(['Transfer', 'Pending', 'Ordered'])],
            'discount' => ['nullable', 'numeric'],
            'shipping' => ['nullable', 'numeric'],
            'note' => ['nullable', 'string'],
            'grand_total' => ['required', 'decimal:2', 'min:0'],
        ]);
        // dd($request->all());

        $transfer = new Transfer();
        $transfer->date = $request->date;
        $transfer->from_warehouse_id = $request->from_warehouse_id;
        $transfer->to_warehouse_id = $request->to_warehouse_id;
        $transfer->status = $request->status;
        $transfer->discount = $request->discount;
        $transfer->shipping = $request->shipping;
        $transfer->note = $request->note;
        $transfer->grand_total = 0;
        $transfer->save();

        foreach ($request->products as $productData) {
            $product = Product::findOrFail($productData['id']);
            $net_unit_cost = $product->price;
            $quantity = $productData['quantity'];
            $discount = $productData['discount'];
            $subtotal = ($net_unit_cost * $quantity) - $discount;

            $transferItem = new TransferItem();
            $transferItem->transfer_id = $transfer->id;
            $transferItem->product_id = $productData['id'];
            $transferItem->net_unit_cost = $net_unit_cost;
            $transferItem->quantity = $quantity;
            $transferItem->stock = $product->product_qty;
            $transferItem->discount = $discount;
            $transferItem->subtotal = $subtotal;
            $transferItem->save();


            //transfer from warehousse//
            Product::where('id', $productData['id'])
                ->where('warehouse_id', $request->from_warehouse_id)
                ->decrement('product_qty', $quantity);

            //existing product - check by code + warehouse OR by name + brand + warehouse
            $existingProduct = Product::where('warehouse_id', $request->to_warehouse_id)
                ->where(function ($q) use ($product) {
                    $q->where('code', $product->code)
                      ->orWhere(function ($q2) use ($product) {
                          $q2->where('name', $product->name)
                             ->where('brand_id', $product->brand_id);
                      });
                })
                ->first();

            if ($existingProduct) {
                $existingProduct->increment('product_qty', $quantity);
            } else {
                // Generate unique code for new product in "to" warehouse (products.code is globally unique)
                $baseCode = $product->code;
                $uniqueCode = $baseCode . '-WH' . $request->to_warehouse_id;
                $counter = 0;
                while (Product::where('code', $uniqueCode)->exists()) {
                    $counter++;
                    $uniqueCode = $baseCode . '-WH' . $request->to_warehouse_id . '-' . $counter;
                }

                Product::create([
                    'name' => $product->name,
                    'code' => $uniqueCode,
                    'image' => $product->image ?? '',
                    'category_id' => $product->category_id,
                    'brand_id' => $product->brand_id,
                    'supplier_id' => $product->supplier_id,
                    'warehouse_id' => $request->to_warehouse_id,
                    'price' => $product->price,
                    'discount_price' => $product->discount_price,
                    'stock_alert' => $product->stock_alert,
                    'product_qty' => $quantity,
                    'status' => 1,
                    'created_at' => now(),
                ]);
            }
        }

        $notify = array(
            'message' => 'Transfer Added Successfully',
            'alert-type' => 'success'

        );
        return redirect()->route('transfer.index')->with($notify);
    }
    //end method//
    public function edit(string $id) {
        $transferData = Transfer::with(['fromwarehouse' ,'towarehouse','transferItems.product'])
                    ->findOrFail($id);
        $warehouses = WareHouse::all();
        return view('admin.backend.transfer.edit',compact('transferData','warehouses'));
    }

    public function update(Request $request, string $id) {
        $request->validate([
            'date' => ['required', 'date'],
            'from_warehouse_id' => ['required', 'exists:ware_houses,id'],
            'to_warehouse_id' => ['required', 'exists:ware_houses,id'],
            'status' => ['required', Rule::in(['Transfer', 'Pending', 'Ordered'])],
            'discount' => ['nullable', 'numeric'],
            'shipping' => ['nullable', 'numeric'],
            'note' => ['nullable', 'string'],
            'grand_total' => ['required', 'decimal:2', 'min:0'],
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $transfer = Transfer::findOrFail($id);

                // 1. Revert Old Stock & Delete Items
                $this->revertStock($transfer);
                TransferItem::where('transfer_id', $transfer->id)->delete();

                // 2. Update Transfer Details
                $transfer->update([
                    'date' => $request->date,
                    'status' => $request->status,
                    'discount' => $request->discount ?? 0,
                    'shipping' => $request->shipping ?? 0,
                    'note' => $request->note,
                    'grand_total' => $request->grand_total,
                ]);

                // 3. Process New Items
                if ($request->has('products')) {
                    foreach ($request->products as $productId => $productData) {
                        $this->processTransferItem($transfer, $productId, $productData, $request->to_warehouse_id);
                    }
                }
            });

            $notify = ['message' => 'Transfer Updated Successfully', 'alert-type' => 'success'];
            return redirect()->route('transfer.index')->with($notify);

        } catch (\Exception $e) {
            $notify = ['message' => 'Error: ' . $e->getMessage(), 'alert-type' => 'error'];
            return redirect()->back()->with($notify);
        }
    }

    /**
     * Revert stock for all items in a transfer (Source +, Destination -)
     */
    private function revertStock($transfer) {
        $items = TransferItem::where('transfer_id', $transfer->id)->get();
        foreach ($items as $item) {
            // Restore Source Stock
            Product::where('id', $item->product_id)->increment('product_qty', $item->quantity);


            $sourceProduct = Product::find($item->product_id);
            if ($sourceProduct) {
                $destProduct = $this->findProductInWarehouse($sourceProduct, $transfer->to_warehouse_id);
                if ($destProduct) {
                    $destProduct->decrement('product_qty', $item->quantity);
                }
            }
        }
    }


    private function processTransferItem($transfer, $productId, $data, $toWarehouseId) {
        $sourceProduct = Product::findOrFail($productId);

        // 1. Create Transfer Item Record
        TransferItem::create([
            'transfer_id' => $transfer->id,
            'product_id' => $productId,
            'net_unit_cost' => $data['net_unit_cost'] ?? $sourceProduct->price,
            'quantity' => $data['quantity'],
            'stock' => $sourceProduct->product_qty,
            'discount' => $data['discount'] ?? 0,
            'subtotal' => $data['subtotal'],
        ]);

        // 2. Decrement Source Stock
        $sourceProduct->decrement('product_qty', $data['quantity']);

        // 3. Increment or Create Destination Stock
        $destProduct = $this->findProductInWarehouse($sourceProduct, $toWarehouseId);

        if ($destProduct) {
            $destProduct->increment('product_qty', $data['quantity']);
        } else {
            $this->createProductInWarehouse($sourceProduct, $toWarehouseId, $data['quantity']);
        }
    }

    /**
     * Find a matching product in a specific warehouse by Code or Name
     */
    private function findProductInWarehouse($sourceProduct, $warehouseId) {
        return Product::where('warehouse_id', $warehouseId)
            ->where(function ($q) use ($sourceProduct) {
                $q->where('code', $sourceProduct->code)
                  ->orWhere(function ($q2) use ($sourceProduct) {
                      $q2->where('name', $sourceProduct->name)
                         ->where('brand_id', $sourceProduct->brand_id);
                  });
            })
            ->first();
    }

    /**
     * Create a new product clone in the destination warehouse
     */
    private function createProductInWarehouse($sourceProduct, $warehouseId, $quantity) {
        $baseCode = $sourceProduct->code;
        $uniqueCode = $baseCode . '-WH' . $warehouseId;

        // Ensure unique code
        $counter = 0;
        while (Product::where('code', $uniqueCode)->exists()) {
            $counter++;
            $uniqueCode = $baseCode . '-WH' . $warehouseId . '-' . $counter;
        }

        Product::create([
            'name' => $sourceProduct->name,
            'code' => $uniqueCode,
            'image' => $sourceProduct->image ?? '',
            'category_id' => $sourceProduct->category_id,
            'brand_id' => $sourceProduct->brand_id,
            'supplier_id' => $sourceProduct->supplier_id,
            'warehouse_id' => $warehouseId,
            'price' => $sourceProduct->price,
            'discount_price' => $sourceProduct->discount_price,
            'stock_alert' => $sourceProduct->stock_alert,
            'product_qty' => $quantity,
            'status' => 1,
            'created_at' => now(),
        ]);
    }
    //end method//
    public function destroy(string $id)  {
       try{
        DB::beginTransaction();
         $transfer = Transfer::findOrFail($id);
        $transferItem  = TransferItem::where('transfer_id',$transfer->id)->get();

        foreach($transferItem as $item){

          Product::where('id',$item->product_id)->where('warehouse_id',$transfer->from_warehouse_id)
            ->increment('product_qty',$item->quantity);
             Product::where('warehouse_id',$transfer->to_warehouse_id)
            ->decrement('product_qty',$item->quantity);
        }
        TransferItem::where('transfer_id',$transfer->id)->delete();
        $transfer->delete();
        DB::commit();
          $notify = ['message' => 'Transfer Updated Successfully', 'alert-type' => 'success'];
            return redirect()->route('transfer.index')->with($notify);
       }
       catch(\Exception $e){
        DB::rollBack();
        return response()->json(['error' => $e->getMessage()],500);

       }
    }
    //end method//
     public function details(string $id) {
        $transfer = Transfer::with(['fromwarehouse' ,'towarehouse','transferItems.product'])
                    ->findOrFail($id);
        return view('admin.backend.transfer.details',compact('transfer'));
    }
}
