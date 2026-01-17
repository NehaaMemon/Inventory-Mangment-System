# Purchase System - Complete Guide (Roman Urdu)

## 📋 **Overview - System Kya Hai?**

Yeh ek **Inventory Management System** hai jahan aap suppliers se products purchase kar sakte hain. System mein:
- **Purchase** = Ek complete order/bill
- **Purchase Items** = Us order mein jo products hain
- **Products** = Jo items aap purchase kar rahe hain

---

## 🗄️ **Database Structure - Tables Kaise Bane Hain**

### 1. **`purchases` Table** (Main Purchase Record)
```
- id: Unique purchase number
- date: Purchase ki date
- warehouse_id: Konsa warehouse (godown)
- supplier_id: Konse supplier se purchase
- discount: Total discount amount
- shipping: Shipping charges
- grand_total: Total bill amount
- note: Koi notes
- status: Received/Pending/Ordered
```

### 2. **`purchase_items` Table** (Purchase ke Products)
```
- id: Item ka unique number
- purchase_id: Kis purchase ka yeh item hai
- product_id: Konsa product hai
- net_unit_cost: Ek product ki price
- stock: Purchase ke baad kitna stock hoga
- quantity: Kitne products purchase kiye
- discount: Is item par discount
- total_price: Is item ka total (quantity × cost - discount)
```

### 3. **`products` Table** (Products ki Details)
```
- id: Product ka unique number
- name: Product ka naam
- code: Product code
- price: Product ki price
- product_qty: Current stock quantity
```

---

## 🔄 **Purchase Create Karne Ka Complete Flow**

### **STEP 1: Frontend (Form Fill Karna)**

User form mein yeh details fill karta hai:
1. **Date** - Purchase ki date
2. **Warehouse** - Konsa warehouse select karna hai
3. **Supplier** - Konse supplier se purchase
4. **Products** - Kaunse products add karne hain
   - Product search karke add karte hain
   - Har product ke liye:
     - Quantity (kitne pieces)
     - Cost (ek piece ki price)
     - Discount (agar hai)
5. **Discount** - Total discount amount
6. **Shipping** - Shipping charges
7. **Status** - Received/Pending/Ordered
8. **Note** - Koi additional notes

### **STEP 2: JavaScript Calculations (Frontend)**

**File:** `public/backend/assets/js/custome.js`

#### **A. Product Add Hone Par:**
```javascript
// Jab product add hota hai:
1. Product table mein row add hoti hai
2. Subtotal calculate hota hai: (cost × quantity) - discount
3. Grand Total update hota hai
```

#### **B. Grand Total Calculation:**
```javascript
function updateGrandTotal() {
    // Sab products ke subtotals ka sum
    let grandTotal = 0;
    
    // Har product ka subtotal add karo
    document.querySelectorAll(".subtotal").forEach(function (item) {
        grandTotal += parseFloat(item.textContent) || 0;
    });
    
    // Discount subtract karo
    let discount = parseFloat(document.getElementById("inputDiscount").value) || 0;
    grandTotal = grandTotal - discount;
    
    // Shipping add karo
    let shipping = parseFloat(document.getElementById("inputShipping").value) || 0;
    grandTotal = grandTotal + shipping;
    
    // Display update karo
    document.getElementById("grandTotal").textContent = `TK ${grandTotal.toFixed(2)}`;
    
    // Hidden input mein value set karo (form submit ke liye)
    document.querySelector("input[name='grand_total']").value = grandTotal.toFixed(2);
}
```

**Formula:**
```
Grand Total = (Sum of all product subtotals) - Discount + Shipping
```

### **STEP 3: Form Submit (Backend Processing)**

**File:** `app/Http/Controllers/backend/PurchaseController.php`

#### **Function:** `store(Request $request)`

#### **A. Validation (Data Check)**
```php
// Pehle check karo ke sab data sahi hai:
- date: Required, valid date
- supplier_id: Required, database mein exist karta hai
- warehouse_id: Required, database mein exist karta hai
- status: Required, sirf Pending/Received/Ordered
- discount: Optional, number
- shipping: Optional, number
- grand_total: Required, minimum 0

// Products ka validation:
- products.*.id: Required, product exist karta hai
- products.*.cost: Required, number, minimum 0
- products.*.quantity: Required, integer, minimum 1
- products.*.discount: Optional, number
```

#### **B. Purchase Record Create Karna**
```php
// Pehle main purchase record create karo (ID generate hone ke liye)
$purchase = new Purchase();
$purchase->date = $request->date;
$purchase->supplier_id = $request->supplier_id;
$purchase->warehouse_id = $request->warehouse_id;
$purchase->discount = $request->discount ?? 0;
$purchase->shipping = $request->shipping ?? 0;
$purchase->status = $request->status;
$purchase->note = $request->note;
$purchase->grand_total = 0; // Abhi 0, baad mein update karenge
$purchase->save(); // IMPORTANT: ID generate hone ke liye save karo
```

**Kyun pehle save karte hain?**
- `purchase_items` table mein `purchase_id` chahiye
- Jab tak purchase save nahi hota, `$purchase->id` available nahi hota
- Isliye pehle save karke ID generate karte hain

#### **C. Products Loop - Har Product Ke Liye**
```php
$grandtotal = 0; // Total amount track karne ke liye

foreach ($request->products as $item) {
    // 1. Product find karo
    $product = Product::findOrFail($item['id']);
    
    // 2. Cost determine karo (form se ya product ki default price)
    $netUnitCost = $item['cost'] ?? $product->price;
    
    // 3. Subtotal calculate karo
    $subtotal = ($netUnitCost * $item['quantity']) - ($item['discount'] ?? 0);
    
    // 4. Grand total mein add karo
    $grandtotal += $subtotal;
    
    // 5. Purchase Item create karo
    $purchaseitem = new PurchaseItem();
    $purchaseitem->purchase_id = $purchase->id;
    $purchaseitem->product_id = $product->id;
    $purchaseitem->net_unit_cost = $netUnitCost;
    $purchaseitem->stock = $product->product_qty + $item['quantity']; // Future stock
    $purchaseitem->quantity = $item['quantity'];
    $purchaseitem->discount = $item['discount'] ?? 0;
    $purchaseitem->total_price = $subtotal;
    $purchaseitem->save();
    
    // 6. Product ki stock update karo (increment)
    $product->increment('product_qty', $item['quantity']);
}
```

**Important Points:**
- Har product ke liye alag `PurchaseItem` record create hota hai
- Product ki stock automatically increase hoti hai
- `stock` column mein future stock save hota hai (current + quantity)

#### **D. Final Grand Total Update**
```php
// Ab final grand total calculate karke update karo
$purchase->update([
    'grand_total' => $grandtotal + ($request->shipping ?? 0) - ($request->discount ?? 0),
]);
```

**Formula:**
```
Final Grand Total = (Sum of all product subtotals) + Shipping - Discount
```

---

## ✏️ **Purchase Edit Karne Ka Flow**

### **Function:** `update(Request $request, string $id)`

### **STEP 1: Transaction Start**
```php
DB::beginTransaction(); // Sab kuch ek saath save hoga ya kuch nahi
```

**Kyun Transaction?**
- Agar koi error aaye to sab changes rollback ho jayenge
- Stock consistency maintain rahegi

### **STEP 2: Purchase Main Record Update**
```php
$purchase = Purchase::findOrFail($id);
$purchase->date = $request->date;
$purchase->supplier_id = $request->supplier_id;
// ... sab fields update
$purchase->save();
```

### **STEP 3: Purani Stock Reverse Karna**
```php
// Purane purchase items ko find karo
$oldPurchaseItems = PurchaseItem::where('purchase_id', $purchase->id)->get();

// Har purane item ke liye stock se quantity hatao
foreach ($oldPurchaseItems as $olditem) {
    $product = Product::find($olditem->product_id);
    if ($product) {
        $product->decrement('product_qty', $olditem->quantity);
    }
}
```

**Kyun?**
- Agar pehle 10 items purchase kiye the, to stock se 10 hatao
- Phir naye quantity se update karenge

### **STEP 4: Purane Items Delete Karna**
```php
PurchaseItem::where('purchase_id', $purchase->id)->delete();
```

### **STEP 5: Naye Items Add Karna**
```php
foreach ($request->products as $productId => $productData) {
    $product = Product::findOrFail($productId);
    
    $quantity = $productData['quantity'];
    $netUnitCost = $productData['net_unit_cost'];
    $discount = $productData['discount'] ?? 0;
    $totalPrice = ($quantity * $netUnitCost) - $discount;
    
    // Naya purchase item create karo
    $purchaseitem = new PurchaseItem();
    $purchaseitem->purchase_id = $purchase->id;
    $purchaseitem->product_id = $productId;
    $purchaseitem->net_unit_cost = $netUnitCost;
    $purchaseitem->stock = $product->product_qty; // Current stock
    $purchaseitem->quantity = $quantity;
    $purchaseitem->discount = $discount;
    $purchaseitem->total_price = $totalPrice;
    $purchaseitem->save();
    
    // Stock increment karo
    $product->increment('product_qty', $quantity);
}
```

### **STEP 6: Transaction Commit**
```php
DB::commit(); // Sab kuch save ho gaya
```

**Agar Error Aaye:**
```php
catch (\Exception $e) {
    DB::rollBack(); // Sab kuch wapas purani halat mein
    // Error message return karo
}
```

---

## 🗑️ **Purchase Delete Karne Ka Flow**

### **Function:** `destroy(string $id)`

```php
// 1. Purchase find karo
$purchase = Purchase::findOrFail($id);

// 2. Purchase items find karo
$purchaseItem = PurchaseItem::where('purchase_id', $id)->get();

// 3. Har item ke liye stock se quantity hatao
foreach ($purchaseItem as $item) {
    $product = Product::find($item->product_id);
    if ($product) {
        $product->decrement('product_qty', $item->quantity);
    }
}

// 4. Purchase items delete karo
PurchaseItem::where('purchase_id', $id)->delete();

// 5. Purchase delete karo
$purchase->delete();
```

**Important:**
- Pehle stock se quantity hatao
- Phir items delete karo
- Phir purchase delete karo

---

## 🔗 **Models - Relationships**

### **Purchase Model**
```php
// Ek purchase ka ek supplier
public function supplier() {
    return $this->belongsTo(Supplier::class);
}

// Ek purchase ka ek warehouse
public function warehouse() {
    return $this->belongsTo(Warehouse::class);
}

// Ek purchase ke multiple items
public function purchaseItems() {
    return $this->hasMany(PurchaseItem::class);
}
```

### **PurchaseItem Model**
```php
// Ek item ka ek product
public function product() {
    return $this->belongsTo(Product::class);
}

// Ek item ka ek purchase
public function purchase() {
    return $this->belongsTo(Purchase::class);
}
```

---

## 📊 **Complete Example - Ek Purchase Kaise Banta Hai**

### **Scenario:**
- **Date:** 2024-01-15
- **Warehouse:** Warehouse 1
- **Supplier:** Supplier ABC
- **Products:**
  - Product A: 10 pieces @ 100 each, discount 50
  - Product B: 5 pieces @ 200 each, no discount
- **Discount:** 100
- **Shipping:** 50

### **Calculation:**

**Product A:**
- Subtotal = (10 × 100) - 50 = 1000 - 50 = 950

**Product B:**
- Subtotal = (5 × 200) - 0 = 1000

**Total Products:** 950 + 1000 = 1950

**Grand Total:** 1950 - 100 (discount) + 50 (shipping) = **1900**

### **Database Records:**

**`purchases` table:**
```
id: 1
date: 2024-01-15
warehouse_id: 1
supplier_id: 1
discount: 100
shipping: 50
grand_total: 1900
status: Received
```

**`purchase_items` table:**
```
Row 1:
- purchase_id: 1
- product_id: 1 (Product A)
- net_unit_cost: 100
- quantity: 10
- discount: 50
- total_price: 950
- stock: (old stock + 10)

Row 2:
- purchase_id: 1
- product_id: 2 (Product B)
- net_unit_cost: 200
- quantity: 5
- discount: 0
- total_price: 1000
- stock: (old stock + 5)
```

---

## ⚠️ **Important Points - Yaad Rakhein**

1. **Stock Management:**
   - Purchase create: Stock increase hoti hai
   - Purchase edit: Pehle purani stock hatao, phir nayi add karo
   - Purchase delete: Stock se quantity hatao

2. **Grand Total Calculation:**
   - Frontend: JavaScript se real-time calculate
   - Backend: Phir se verify karke save

3. **Transaction Use:**
   - Edit mein transaction use karo taake consistency rahe

4. **ID Generation:**
   - Purchase pehle save karo (ID generate hone ke liye)
   - Phir purchase_items create karo

5. **Validation:**
   - Har step par validation zaroori hai
   - Invalid data se bachao

---

## 🐛 **Common Issues Aur Solutions**

### **Issue 1: Grand Total Clear Ho Raha Hai**
**Solution:** JavaScript mein event listeners properly set karo, duplicate listeners remove karo

### **Issue 2: Stock Update Nahi Ho Raha**
**Solution:** Check karo ke `increment()` function properly call ho raha hai

### **Issue 3: Purchase Items Save Nahi Ho Rahe**
**Solution:** Check karo ke `purchase_id` properly set ho raha hai, purchase pehle save ho chuka hai

---

## 📝 **Summary - Quick Reference**

**Purchase Create:**
1. Form fill karo
2. JavaScript grand total calculate karta hai
3. Form submit
4. Validation
5. Purchase record create (ID generate)
6. Har product ke liye:
   - PurchaseItem create
   - Product stock increment
7. Grand total update

**Purchase Edit:**
1. Transaction start
2. Purchase update
3. Purani stock reverse
4. Purane items delete
5. Naye items add + stock increment
6. Transaction commit

**Purchase Delete:**
1. Stock se quantity hatao
2. Items delete
3. Purchase delete

---

**Agar aur koi sawal ho to pooch sakte hain!** 🚀

