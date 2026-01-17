# Purchase Code Examples - Step by Step (Roman Urdu)

## 📝 **Complete Code Explanation with Examples**

### **1. Purchase Create Function - Detailed Explanation**

```php
public function store(Request $request)
{
    try {
        // ============================================
        // STEP 1: VALIDATION - Data Check Karna
        // ============================================
        
        // Pehle main purchase fields validate karo
        $request->validate([
            'date' => ['required', 'date'],  // Date zaroori hai, valid date honi chahiye
            'supplier_id' => ['required', 'exists:suppliers,id'],  // Supplier select karna zaroori, database mein exist karna chahiye
            'warehouse_id' => ['required', 'exists:ware_houses,id'],  // Warehouse select karna zaroori
            'status' => ['required', Rule::in(['Pending','Received','Ordered'])],  // Status sirf yeh 3 values mein se ho sakta hai
            'discount' => ['nullable','numeric'],  // Discount optional hai, number hona chahiye
            'shipping' => ['nullable','numeric'],  // Shipping optional hai, number hona chahiye
            'note' => ['nullable','string'],  // Note optional hai, string honi chahiye
            'grand_total' => ['required','decimal:2','min:0'],  // Grand total zaroori hai, minimum 0
        ]);

        // Ab products ka validation
        $request->validate([
            'products.*.id' => ['required','exists:products,id'],  // Har product ka ID zaroori, database mein exist karna chahiye
            'products.*.cost' => ['required','numeric','min:0'],  // Har product ki cost zaroori, number honi chahiye, minimum 0
            'products.*.quantity' => ['required','integer','min:1'],  // Quantity zaroori, integer honi chahiye, minimum 1
            'products.*.discount' => ['nullable','numeric'],  // Product discount optional hai
        ]);

        // ============================================
        // STEP 2: PURCHASE MAIN RECORD CREATE
        // ============================================
        
        // Naya Purchase object create karo
        $purchase = new Purchase();
        
        // Form se data leke set karo
        $purchase->date = $request->date;  // Purchase ki date
        $purchase->supplier_id = $request->supplier_id;  // Konse supplier se purchase
        $purchase->warehouse_id = $request->warehouse_id;  // Konsa warehouse
        $purchase->discount = $request->discount ?? 0;  // Discount, agar nahi hai to 0
        $purchase->shipping = $request->shipping ?? 0;  // Shipping, agar nahi hai to 0
        $purchase->status = $request->status;  // Status (Pending/Received/Ordered)
        $purchase->note = $request->note;  // Koi notes
        $purchase->grand_total = 0;  // Abhi 0 set karo, baad mein calculate karke update karenge
        
        // ⚠️ IMPORTANT: Pehle save karo taake ID generate ho
        // Kyunki purchase_items table mein purchase_id chahiye
        $purchase->save();

        // ============================================
        // STEP 3: GRAND TOTAL TRACK KARNE KE LIYE
        // ============================================
        
        $grandtotal = 0;  // Total amount track karne ke liye variable

        // ============================================
        // STEP 4: LOOP - HAR PRODUCT KE LIYE
        // ============================================
        
        // Form se jo products aaye hain, unko loop karo
        foreach ($request->products as $item) {
            
            // 4.1: Product find karo database se
            $product = Product::findOrFail($item['id']);
            // findOrFail() agar product nahi mila to error dega
            
            // 4.2: Cost determine karo
            // Agar form se cost aayi hai to wo use karo, warna product ki default price
            $netUnitCost = $item['cost'] ?? $product->price;
            
            // 4.3: Subtotal calculate karo
            // Formula: (Cost × Quantity) - Discount
            $subtotal = ($netUnitCost * $item['quantity']) - ($item['discount'] ?? 0);
            
            // Example:
            // Cost = 100, Quantity = 10, Discount = 50
            // Subtotal = (100 × 10) - 50 = 1000 - 50 = 950
            
            // 4.4: Grand total mein add karo
            $grandtotal += $subtotal;
            // Har product ka subtotal grand total mein add hota rahega

            // ============================================
            // STEP 5: PURCHASE ITEM CREATE
            // ============================================
            
            // Naya PurchaseItem object create karo
            $purchaseitem = new PurchaseItem();
            
            // Data set karo
            $purchaseitem->purchase_id = $purchase->id;  // Kis purchase ka yeh item hai
            $purchaseitem->product_id = $product->id;  // Konsa product hai
            $purchaseitem->net_unit_cost = $netUnitCost;  // Ek piece ki cost
            $purchaseitem->stock = $product->product_qty + $item['quantity'];  // Future stock (current + quantity)
            $purchaseitem->quantity = $item['quantity'];  // Kitne pieces purchase kiye
            $purchaseitem->discount = $item['discount'] ?? 0;  // Is item par discount
            $purchaseitem->total_price = $subtotal;  // Is item ka total price
            
            // Database mein save karo
            $purchaseitem->save();

            // ============================================
            // STEP 6: PRODUCT STOCK UPDATE
            // ============================================
            
            // Product ki stock quantity increase karo
            $product->increment('product_qty', $item['quantity']);
            // increment() function automatically current value mein add kar deta hai
            
            // Example:
            // Old stock: 50
            // Purchase quantity: 10
            // New stock: 50 + 10 = 60
        }

        // ============================================
        // STEP 7: FINAL GRAND TOTAL UPDATE
        // ============================================
        
        // Ab final grand total calculate karke purchase record update karo
        $purchase->update([
            'grand_total' => $grandtotal + ($request->shipping ?? 0) - ($request->discount ?? 0),
        ]);
        
        // Formula:
        // Grand Total = (Sum of all product subtotals) + Shipping - Discount
        
        // Example:
        // Product subtotals: 950 + 1000 = 1950
        // Shipping: 50
        // Discount: 100
        // Grand Total: 1950 + 50 - 100 = 1900

        // ============================================
        // STEP 8: SUCCESS MESSAGE
        // ============================================
        
        $notify = array(
            'message' => 'Purchase Added Successfully',
            'alert-type' => 'success'
        );
        
        // Purchase list page par redirect karo success message ke saath
        return redirect()->route('purchase.index')->with($notify);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        // Agar validation fail ho to error log karo
        Log::error($e->getMessage() . "\n" . $e->getTraceAsString());
        
        // JSON response return karo (Ajax debugging ke liye)
        return response()->json(['errors' => $e->errors()], 422);
    }
}
```

---

### **2. Purchase Edit Function - Detailed Explanation**

```php
public function update(Request $request, string $id)
{
    // ============================================
    // STEP 1: VALIDATION
    // ============================================
    
    $request->validate([
        'date' => ['required', 'date'],
        'supplier_id' => ['required', 'exists:suppliers,id'],
        'warehouse_id' => ['required', 'exists:ware_houses,id'],
        'status' => ['required', Rule::in(['Pending', 'Received', 'Ordered'])],
        'grand_total' => ['required', 'numeric', 'min:0'],
        'products' => ['required', 'array'],  // Products array zaroori hai
    ]);

    // ============================================
    // STEP 2: TRANSACTION START
    // ============================================
    
    // Transaction start karo
    // Transaction ka matlab: Ya to sab kuch save hoga, ya kuch nahi
    // Agar beech mein error aaye to sab changes rollback ho jayenge
    DB::beginTransaction();

    try {
        // ============================================
        // STEP 3: PURCHASE MAIN RECORD UPDATE
        // ============================================
        
        // Existing purchase find karo
        $purchase = Purchase::findOrFail($id);
        
        // Form se naya data leke update karo
        $purchase->date = $request->date;
        $purchase->supplier_id = $request->supplier_id;
        $purchase->warehouse_id = $request->warehouse_id;
        $purchase->discount = $request->discount ?? 0;
        $purchase->shipping = $request->shipping ?? 0;
        $purchase->status = $request->status;
        $purchase->note = $request->note;
        $purchase->grand_total = $request->grand_total;  // Form se grand total le lo
        $purchase->save();

        // ============================================
        // STEP 4: PURANI STOCK REVERSE KARNA
        // ============================================
        
        // Purane purchase items find karo
        $oldPurchaseItems = PurchaseItem::where('purchase_id', $purchase->id)->get();
        
        // Har purane item ke liye stock se quantity hatao
        foreach ($oldPurchaseItems as $olditem) {
            $product = Product::find($olditem->product_id);
            
            if ($product) {
                // Purani quantity stock se hatao (decrement)
                $product->decrement('product_qty', $olditem->quantity);
                
                // Example:
                // Pehle purchase: 10 items the
                // Current stock: 60
                // After decrement: 60 - 10 = 50
            }
        }

        // ============================================
        // STEP 5: PURANE ITEMS DELETE KARNA
        // ============================================
        
        // Purane purchase items ko delete karo
        PurchaseItem::where('purchase_id', $purchase->id)->delete();
        // Kyunki hum naye items add karenge

        // ============================================
        // STEP 6: NAYE ITEMS ADD KARNA + STOCK UPDATE
        // ============================================
        
        // Form se jo naye products aaye hain, unko loop karo
        foreach ($request->products as $productId => $productData) {

            // 6.1: Product find karo
            $product = Product::findOrFail($productId);

            // 6.2: Values clean karo
            $quantity = $productData['quantity'];  // Kitne pieces
            $netUnitCost = $productData['net_unit_cost'];  // Ek piece ki cost
            $discount = $productData['discount'] ?? 0;  // Discount, agar nahi hai to 0

            // 6.3: Total price calculate karo
            $totalPrice = ($quantity * $netUnitCost) - $discount;
            // Formula: (Quantity × Cost) - Discount

            // 6.4: Purchase Item Create Karo
            $purchaseitem = new PurchaseItem();
            $purchaseitem->purchase_id = $purchase->id;
            $purchaseitem->product_id = $productId;
            $purchaseitem->net_unit_cost = $netUnitCost;
            $purchaseitem->stock = $product->product_qty;  // Current stock (decrement ke baad)
            $purchaseitem->quantity = $quantity;
            $purchaseitem->discount = $discount;
            $purchaseitem->total_price = $totalPrice;
            $purchaseitem->save();

            // 6.5: Stock Increment Karo (Naya Stock Add)
            $product->increment('product_qty', $quantity);
            
            // Example:
            // After decrement: 50
            // New quantity: 15
            // After increment: 50 + 15 = 65
        }

        // ============================================
        // STEP 7: TRANSACTION COMMIT
        // ============================================
        
        // Agar sab kuch sahi hai to commit karo (save karo)
        DB::commit();

        // Success message
        $notify = array(
            'message' => 'Purchase Updated Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('purchase.index')->with($notify);

    } catch (\Exception $e) {
        // ============================================
        // STEP 8: ERROR HANDLING - ROLLBACK
        // ============================================
        
        // Agar koi error aaya to sab changes rollback karo
        // Matlab sab kuch purani halat mein wapas
        DB::rollBack();

        // Error log karo
        Log::error($e->getMessage());

        // Error message ke saath wapas redirect karo
        return redirect()->back()->with('error', 'Update Failed: ' . $e->getMessage());
    }
}
```

---

### **3. Purchase Delete Function - Detailed Explanation**

```php
function destroy(string $id)
{
    try {
        // ============================================
        // STEP 1: PURCHASE FIND KARO
        // ============================================
        
        $purchase = Purchase::findOrFail($id);
        // findOrFail() agar purchase nahi mila to 404 error dega

        // ============================================
        // STEP 2: PURCHASE ITEMS FIND KARO
        // ============================================
        
        $purchaseItem = PurchaseItem::where('purchase_id', $id)->get();
        // Is purchase ke sab items find karo

        // ============================================
        // STEP 3: STOCK SE QUANTITY HATAO
        // ============================================
        
        // Har item ke liye stock se quantity hatao
        foreach ($purchaseItem as $item) {
            $product = Product::find($item->product_id);
            
            if ($product) {
                // Product ki stock se purchased quantity hatao
                $product->decrement('product_qty', $item->quantity);
                
                // Example:
                // Current stock: 65
                // Purchased quantity: 15
                // After decrement: 65 - 15 = 50
            }
        }

        // ============================================
        // STEP 4: PURCHASE ITEMS DELETE KARO
        // ============================================
        
        PurchaseItem::where('purchase_id', $id)->delete();
        // Is purchase ke sab items delete karo

        // ============================================
        // STEP 5: PURCHASE DELETE KARO
        // ============================================
        
        $purchase->delete();
        // Ab main purchase record delete karo

        // Success message
        $notify = array(
            'message' => 'Purchase Deleted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('purchase.index')->with($notify);

    } catch (\Exception $e) {
        // Error handling
        // Agar koi error aaye to catch block mein handle karo
    }
}
```

---

### **4. JavaScript Grand Total Calculation - Detailed Explanation**

```javascript
// Grand total update function
function updateGrandTotal() {
    // ============================================
    // STEP 1: INITIALIZE GRAND TOTAL
    // ============================================
    
    let grandTotal = 0;  // Pehle 0 se start karo

    // ============================================
    // STEP 2: SAB PRODUCTS KE SUBTOTALS KA SUM
    // ============================================
    
    // Table mein jo bhi products hain, unke subtotals ko add karo
    document.querySelectorAll(".subtotal").forEach(function (item) {
        // Har subtotal cell se value lo
        let subtotalValue = parseFloat(item.textContent) || 0;
        // parseFloat() string ko number mein convert karta hai
        // || 0 agar NaN ho to 0 use karo
        
        // Grand total mein add karo
        grandTotal += subtotalValue;
    });

    // Example:
    // Product A subtotal: 950
    // Product B subtotal: 1000
    // grandTotal = 0 + 950 + 1000 = 1950

    // ============================================
    // STEP 3: DISCOUNT AUR SHIPPING VALUES LO
    // ============================================
    
    // Discount input se value lo
    let discount = parseFloat(document.getElementById("inputDiscount").value) || 0;
    
    // Shipping input se value lo
    let shipping = parseFloat(document.getElementById("inputShipping").value) || 0;

    // ============================================
    // STEP 4: GRAND TOTAL CALCULATE KARO
    // ============================================
    
    // Formula: Grand Total = Subtotal - Discount + Shipping
    grandTotal = grandTotal - discount + shipping;
    
    // Example:
    // Subtotal: 1950
    // Discount: 100
    // Shipping: 50
    // Grand Total: 1950 - 100 + 50 = 1900

    // ============================================
    // STEP 5: NEGATIVE VALUE CHECK
    // ============================================
    
    // Grand total kabhi negative nahi hona chahiye
    if (grandTotal < 0) {
        grandTotal = 0;  // Agar negative ho to 0 set karo
    }

    // ============================================
    // STEP 6: DISPLAY UPDATE KARO
    // ============================================
    
    // Grand total display element ko update karo
    let grandTotalElement = document.getElementById("grandTotal");
    if (grandTotalElement) {
        // Format: "TK 1900.00"
        grandTotalElement.textContent = `TK ${grandTotal.toFixed(2)}`;
        // toFixed(2) 2 decimal places tak show karta hai
    }

    // ============================================
    // STEP 7: HIDDEN INPUT UPDATE KARO
    // ============================================
    
    // Form submit ke liye hidden input mein value set karo
    let grandTotalInput = document.querySelector("input[name='grand_total']");
    if (grandTotalInput) {
        grandTotalInput.value = grandTotal.toFixed(2);
        // Backend ko yeh value milegi
    }
}
```

---

### **5. Product Add Karne Ka JavaScript Function**

```javascript
// Product ko table mein add karne ka function
function addProductToTable(productElement) {
    // ============================================
    // STEP 1: PRODUCT DATA EXTRACT KARO
    // ============================================
    
    let productId = productElement.getAttribute("data-id");
    let productCode = productElement.getAttribute("data-code");
    let productName = productElement.getAttribute("data-name");
    let netUnitCost = parseFloat(productElement.getAttribute("data-cost"));
    let stock = parseInt(productElement.getAttribute("data-stock"));

    // ============================================
    // STEP 2: DUPLICATE CHECK
    // ============================================
    
    // Check karo ke yeh product pehle se table mein to nahi hai
    if (document.querySelector(`tr[data-id="${productId}"]`)) {
        alert("Product already added.");
        return;  // Agar already hai to return karo
    }

    // ============================================
    // STEP 3: TABLE ROW CREATE KARO
    // ============================================
    
    let row = `
      <tr data-id="${productId}">
          <td>
              ${productCode} - ${productName}
              <input type="hidden" name="products[${productId}][id]" value="${productId}">
              <input type="hidden" name="products[${productId}][name]" value="${productName}">
              <input type="hidden" name="products[${productId}][code]" value="${productCode}">
          </td>
          <td>${netUnitCost.toFixed(2)}
              <input type="hidden" name="products[${productId}][cost]" value="${netUnitCost}">
          </td>
          <td style="color:#ffc121">${stock}</td>
          <td>
              <div class="input-group">
                  <button class="btn btn-outline-secondary decrement-qty" type="button">−</button>
                  <input type="text" class="form-control text-center qty-input"
                      name="products[${productId}][quantity]" value="1" min="1" max="${stock}"
                      data-cost="${netUnitCost}" style="width: 30px;">
                  <button class="btn btn-outline-secondary increment-qty" type="button">+</button>
              </div>
          </td>
          <td>
              <input type="number" class="form-control discount-input"
                  name="products[${productId}][discount]" value="0" min="0" style="width:100px">
          </td>
          <td class="subtotal">${netUnitCost.toFixed(2)}</td>
          <td><button class="btn btn-danger btn-sm remove-product">Delete</button></td>
      </tr>
  `;

    // ============================================
    // STEP 4: ROW TABLE MEIN ADD KARO
    // ============================================
    
    orderItemsTableBody.innerHTML += row;
    
    // Search list clear karo
    productList.innerHTML = "";
    productSearchInput.value = "";

    // ============================================
    // STEP 5: EVENT LISTENERS UPDATE KARO
    // ============================================
    
    updateEvents();  // Quantity, discount ke liye event listeners add karo
    updateGrandTotal();  // Grand total update karo
}
```

---

## 🎯 **Key Points - Yaad Rakhein**

1. **Purchase pehle save karo** - ID generate hone ke liye
2. **Stock management** - Har operation mein stock update karo
3. **Transaction use karo** - Edit mein consistency ke liye
4. **Validation zaroori hai** - Invalid data se bachao
5. **Grand total calculation** - Frontend aur backend dono mein verify karo

---

**Yeh examples se aapko code samajhne mein madad milegi!** 💻

