@extends('admin.admin_master')
@section('admin')

<div class="content d-flex flex-column flex-column-fluid">
   <div class="d-flex flex-column-fluid">
      <div class="container-fluid my-4">
         <div class="d-md-flex align-items-center justify-content-between">
            <h3 class="mb-0">Edit Sale Return</h3>
            <div class="text-end my-2 mt-md-0"><a class="btn btn-outline-primary" href="{{ route('sale-return.index') }}">Back</a></div>
         </div>


 <div class="card">
    <div class="card-body">

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Validation Errors:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('sale-return.update',$saleReturn->id)}}" method="post" enctype="multipart/form-data" id="saleReturnUpdateForm" >
       @csrf

@method('put')
<div class="row">
 <div class="col-xl-12">
    <div class="card">
       <div class="row">
          <div class="col-md-4 mb-3">
             <label class="form-label">Date:  <span class="text-danger">*</span></label>
             <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" class="form-control" value="{{ $saleReturn->date }}">
             @error('date')

             <span class="text-danger">{{ $message }}</span>
             @enderror
          </div>

        <input type="hidden" name="warehouse_id" value="{{ $saleReturn->warehouse_id }}">

          <div class="col-md-4 mb-3">
                <div class="form-group w-100">
                <label class="form-label" for="formBasic">Warehouse : <span class="text-danger">*</span></label>
                <select name="warehouse_id" id="warehouse_id" class="form-control form-select" disabled>
    <option value="">Select Warehouse</option>
    @foreach ($warehouses as $item)
    <option value="{{ $item->id }}" {{ $saleReturn->warehouse_id == $item->id ? 'selected' : '' }} >{{ $item->name }}</option>
    @endforeach
                </select>
                <small id="warehouse_error" class="text-danger d-none">Please select the first warehouse.</small>
                </div>
          </div>

          <div class="col-md-4 mb-3">
             <div class="form-group w-100">
                <label class="form-label" for="formBasic">Customer : <span class="text-danger">*</span></label>
                <select name="customer_id" id="customer_id" class="form-control form-select" >
                   <option value="">Select Customer</option>
                   @foreach ($customers as $item)
                   <option value="{{ $item->id }}" {{ $saleReturn->customer_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                   @endforeach
                </select>
             </div>
          </div>
       </div>


       <div class="row">
          <div class="col-md-12 mb-3">
             <label class="form-label">Product:</label>
             <div class="input-group">
                   <span class="input-group-text">
                      <i class="fas fa-search"></i>
                   </span>
                   <input type="search" id="product_search" name="search" class="form-control" placeholder="Search product by code or name">
             </div>
             <div id="product_list" class="list-group mt-2"></div>
          </div>
       </div>




  <div class="row">
     <div class="col-md-12">
        <label class="form-label">Order items: <span class="text-danger">*</span></label>
        <table class="table table-striped table-bordered dataTable" style="width: 100%;">
           <thead>
              <tr role="row">
                 <th>Product</th>
                 <th>Net Unit Cost</th>
                 <th>Stock</th>
                 <th>Qty</th>
                 <th>Discount</th>
                 <th>Subtotal</th>
                 <th>Action</th>
              </tr>
           </thead>
           <tbody id="productBody">
    @foreach ($saleReturn->saleReturnItems as $item)
    <tr data-id={{ $item->id  }}>

        <td class="d-flex align-items-center gap-2">
            <input type="text" class="form-control" value="{{ $item->product->code }} - {{ $item->product->name }}" readonly style="max-width: 300px" >
            <button type="button" class="btn btn-primary btn-sm edit-discount-btn"
            data-id="{{ $item->id }}"
            data-name="{{ $item->product->name }}"
            data-cost="{{ $item->net_unit_cost }}"
            data-bs-toggle="modal" data-bs-target="#discountModal" >
            <span class="mdi mdi-book-edit "></span>
            </button>
        </td>

    <td>
        <input type="number" name="products[{{ $item->product->id }}][net_unit_cost]" class="form-control net-cost" value="{{ $item->net_unit_cost }}" style="max-width: 90px;" readonly>

    </td>
    <td>
        <input type="number" name="products[{{ $item->product->id }}][stock]" class="form-control" value="{{ $item->product->product_qty }}" style="max-width: 80px;" readonly>
    </td>

    <td>
        <div class="input-group">
            <button class="btn btn-outline-secondary decrement-qty" type="button">−</button>
            <input type="number" step="1" class="form-control text-center qty-input"
                name="products[{{ $item->product->id }}][quantity]" value="{{ (int)$item->quantity }}" min="1" max="{{ $item->stock }}"
                data-cost="{{ $item->net_unit_cost }}" style="max-width: 50px;">
            <button class="btn btn-outline-secondary increment-qty" type="button">+</button>
        </div>
    </td>

    <td>
        <input type="number" class="form-control discount-input"
            name="products[{{ $item->product->id }}][discount]" value="{{ $item->discount }}" style="max-width: 100px;">
    </td>

    <td class="subtotal">{{ number_format($item->subtotal,2) }}</td>
    <input type="hidden" name="products[{{ $item->product->id }}][subtotal]" value="{{ $item->subtotal }}">

    <td><button type="button" class="btn btn-danger btn-sm remove-item" data-id="{{ $item->id }}"><span class="mdi mdi-delete-circle mdi-18px"></span></button></td>

    </tr>

    @endforeach

           </tbody>
        </table>
     </div>
  </div>

<div class="row">
 <div class="col-md-6 ms-auto">
    <div class="card">
       <div class="card-body pt-7 pb-2">
          <div class="table-responsive">
             <table class="table border">
                <tbody>
                   <tr>
                      <td class="py-3">Discount</td>
                      <td class="py-3" id="displayDiscount">TK {{ $saleReturn->discount }}</td>
                   </tr>
                   <tr>
                      <td class="py-3">Shipping</td>
                      <td class="py-3" id="shippingDisplay">TK {{ $saleReturn->shipping }}</td>
                   </tr>
                   <tr>
                      <td class="py-3 text-primary">Grand Total</td>
                      <td class="py-3 text-primary" id="grandTotal">TK {{ $saleReturn->grand_total }}</td>
                      <input type="hidden" id="grandTotalInput" name="grand_total" value="{{ $saleReturn->grand_total }}">

                   </tr>


                  <tr >
                      <td class="py-3">Paid Amount</td>
                      <td class="py-3" id="paidAmount">
                      <input type="number" step="0.01" name="paid_amount" id="paidAmountInput"
                      placeholder="Enter amount paid" class="form-control" value="{{ $saleReturn->paid_amount ?? 0 }}">
                      </td>
                   </tr>
                   <!-- new add full paid functionality  -->
                   <tr >
                      <td class="py-3">Full Paid</td>
                      <td class="py-3" id="fullPaid">
                         <input type="number" step="0.01" name="full_paid" class="form-control" id="fullPaidInput" value="{{ $saleReturn->full_paid ?? 0 }}">
                      </td>
                   </tr>
                   <tr >
                      <td class="py-3">Due Amount</td>
                      <td class="py-3" id="dueAmount">TK {{ number_format($saleReturn->due_amount, 2) }}</td>
                      <input type="hidden" name="due_amount" id="dueAmountInput" value="{{ $saleReturn->due_amount }}">

                   </tr>


                </tbody>
             </table>
          </div>
       </div>
    </div>
 </div>
</div>


      <div class="row">
         <div class="col-md-4">
            <label class="form-label">Discount: </label>
            <input type="number" id="inputDiscount" name="discount" class="form-control" value="{{ $saleReturn->discount }}">
         </div>
         <div class="col-md-4">
            <label class="form-label">Shipping: </label>
            <input type="number" id="inputShipping" name="shipping" class="form-control" value="{{ $saleReturn->shipping }}">
         </div>
         <div class="col-md-4">
            <div class="form-group w-100">
               <label class="form-label" for="formBasic">Status : <span class="text-danger">*</span></label>
               <select name="status" id="status" class="form-control form-select">
                  <option value="">Select Status</option>
                  <option value="Return" {{ $saleReturn->status == 'Return' ? 'selected' : '' }} >Return</option>
                  <option value="Pending"  {{ $saleReturn->status == 'Pending' ? 'selected' : '' }} >Pending</option>
                  <option value="Ordered" {{ $saleReturn->status == 'Ordered' ? 'selected' : '' }} >Ordered</option>
               </select>
               @error('status')
                  <span class="text-danger">{{ $message }}</span>
               @enderror
            </div>
         </div>
      </div>

      <div class="col-md-12 mt-2">
         <label class="form-label">Notes: </label>
         <textarea class="form-control" name="note" rows="3" placeholder="Enter Notes">{{ $saleReturn->note }}</textarea>
      </div>
   </div>
</div>
</div>

     <div class="col-xl-12">
        <div class="d-flex mt-5 justify-content-end">
           <button class="btn btn-primary me-3" type="submit">Save</button>
           <a class="btn btn-secondary" href="{{ route('sale-return.index') }}">Cancel</a>
        </div>
     </div>
  </div>
</form>
            </div>
         </div>
      </div>
   </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const productBody = document.getElementById("productBody");

    // Update subtotal when quantity or discount changes
    productBody.addEventListener("input", function (e) {
        if (e.target.classList.contains("qty-input")) {
            // Ensure quantity is always an integer
            let qtyValue = parseFloat(e.target.value) || 1;
            qtyValue = Math.max(1, Math.round(qtyValue));
            e.target.value = qtyValue;

            let row = e.target.closest("tr");
            updateSubtotal(row);
        } else if (e.target.classList.contains("discount-input")) {
            let row = e.target.closest("tr");
            updateSubtotal(row);
        }
    });

    // Also handle blur event to ensure integer on focus loss
    productBody.addEventListener("blur", function (e) {
        if (e.target.classList.contains("qty-input")) {
            let qtyValue = parseFloat(e.target.value) || 1;
            qtyValue = Math.max(1, Math.round(qtyValue));
            e.target.value = qtyValue;
            updateSubtotal(e.target.closest("tr"));
        }
    }, true);

    // Increment / decrement buttons - Direct attachment + event delegation
    function setupQtyButtons() {
        // Direct attachment for existing buttons
        productBody.querySelectorAll(".increment-qty").forEach(function(btn) {
            btn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                let input = this.closest(".input-group").querySelector(".qty-input");
                let row = this.closest("tr");
                if (input && row) {
                    let currentVal = Math.round(parseFloat(input.value) || 1);
                    input.value = currentVal + 1;
                    updateSubtotal(row);
                }
                return false;
            };
        });

        productBody.querySelectorAll(".decrement-qty").forEach(function(btn) {
            btn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                let input = this.closest(".input-group").querySelector(".qty-input");
                let row = this.closest("tr");
                if (input && row) {
                    let min = parseInt(input.getAttribute("min")) || 1;
                    let currentVal = Math.round(parseFloat(input.value) || 1);
                    if (currentVal > min) {
                        input.value = currentVal - 1;
                        updateSubtotal(row);
                    }
                }
                return false;
            };
        });
    }

    // Setup buttons on page load
    setupQtyButtons();

    // Also use event delegation as backup
    productBody.addEventListener("click", function(e) {
        if (e.target.classList.contains("increment-qty") || e.target.closest(".increment-qty")) {
            e.preventDefault();
            e.stopPropagation();
            let btn = e.target.classList.contains("increment-qty") ? e.target : e.target.closest(".increment-qty");
            let input = btn.closest(".input-group").querySelector(".qty-input");
            let row = btn.closest("tr");
            if (input && row) {
                let currentVal = Math.round(parseFloat(input.value) || 1);
                input.value = currentVal + 1;
                updateSubtotal(row);
            }
            return false;
        }

        if (e.target.classList.contains("decrement-qty") || e.target.closest(".decrement-qty")) {
            e.preventDefault();
            e.stopPropagation();
            let btn = e.target.classList.contains("decrement-qty") ? e.target : e.target.closest(".decrement-qty");
            let input = btn.closest(".input-group").querySelector(".qty-input");
            let row = btn.closest("tr");
            if (input && row) {
                let min = parseInt(input.getAttribute("min")) || 1;
                let currentVal = Math.round(parseFloat(input.value) || 1);
                if (currentVal > min) {
                    input.value = currentVal - 1;
                    updateSubtotal(row);
                }
            }
            return false;
        }
    });

    function updateSubtotal(row) {
        let qtyInput = row.querySelector(".qty-input");
        if (!qtyInput) return;

        let qty = parseFloat(qtyInput.value) || 0;

        // Ensure quantity is an integer (round it)
        qty = Math.max(1, Math.round(qty));
        // Update the input value to be integer
        qtyInput.value = qty;

        let cost = parseFloat(qtyInput.dataset.cost) || 0;
        let discountInput = row.querySelector(".discount-input");
        let discount = discountInput ? (parseFloat(discountInput.value) || 0) : 0;

        let subtotal = (qty * cost) - discount;

        // Update display
        let subtotalDisplay = row.querySelector(".subtotal");
        if (subtotalDisplay) {
            subtotalDisplay.textContent = subtotal.toFixed(2);
        }

        // Update hidden input for backend - CRITICAL!
        let subtotalHiddenInput = row.querySelector("input[name*='[subtotal]']");
        if (subtotalHiddenInput) {
            subtotalHiddenInput.value = subtotal.toFixed(2);
        } else {
            console.error("Subtotal hidden input not found for row!");
        }

        updateGrandTotal();
    }

       // Grand total update function
       function updateGrandTotal() {
          let grandTotal = 0;

          // Calculate subtotal sum - remove any formatting (commas, spaces, TK, etc.)
          document.querySelectorAll(".subtotal").forEach(function (item) {
             let subtotalText = item.textContent || item.innerText || '0';
             // Remove any non-numeric characters except decimal point
             subtotalText = subtotalText.replace(/[^\d.-]/g, '');
             let subtotalValue = parseFloat(subtotalText) || 0;
             grandTotal += subtotalValue;
          });

          // Get discount and shipping values
          let discountInput = document.getElementById("inputDiscount");
          let shippingInput = document.getElementById("inputShipping");
          let discount = discountInput ? (parseFloat(discountInput.value) || 0) : 0;
          let shipping = shippingInput ? (parseFloat(shippingInput.value) || 0) : 0;

          // Apply discount and add shipping cost
          grandTotal = grandTotal - discount + shipping;

          // Ensure grand total is not negative
          if (grandTotal < 0) {
             grandTotal = 0;
          }

          // Update Grand Total display
          let grandTotalElement = document.getElementById("grandTotal");
          if (grandTotalElement) {
              grandTotalElement.textContent = `TK ${grandTotal.toFixed(2)}`;
          }

          // Also update the hidden input field - THIS IS CRITICAL!
          let grandTotalInput = document.getElementById("grandTotalInput");
          if (grandTotalInput) {
              grandTotalInput.value = grandTotal.toFixed(2);
          }

          // Update due amount when grand total changes
          updateDueAmount();
       }

       // Update due amount function
       function updateDueAmount() {
          let grandTotalInput = document.querySelector("input[name='grand_total']");
          let paidAmountInput = document.getElementById("paidAmountInput");
          let fullPaidInput = document.getElementById("fullPaidInput");

          let grandTotal = parseFloat(grandTotalInput ? grandTotalInput.value : 0) || 0;
          let paidAmount = parseFloat(paidAmountInput ? paidAmountInput.value : 0) || 0;
          let fullPaid = parseFloat(fullPaidInput ? fullPaidInput.value : 0) || 0;

          // Validate inputs
          if (fullPaid < 0) {
             fullPaid = 0;
             if (fullPaidInput) fullPaidInput.value = 0;
          }

          if (paidAmount < 0) {
             paidAmount = 0;
             if (paidAmountInput) paidAmountInput.value = 0;
          }

          // Calculate due amount
          let dueAmount = grandTotal - (paidAmount + fullPaid);

          if (dueAmount < 0) {
             dueAmount = 0;
          }

          // Update due amount display
          let dueAmountElement = document.getElementById("dueAmount");
          if (dueAmountElement) {
             dueAmountElement.textContent = `TK ${dueAmount.toFixed(2)}`;
          }

          // Update hidden input for backend
          let dueAmountInput = document.getElementById("dueAmountInput");
          if (dueAmountInput) {
             dueAmountInput.value = dueAmount.toFixed(2);
          }
       }

       // Event listeners for discount and shipping input change
       document.getElementById("inputDiscount").addEventListener("input", function () {
           let discountValue = parseFloat(this.value) || 0;
           document.getElementById("displayDiscount").textContent = `TK ${discountValue.toFixed(2)}`;
           updateGrandTotal();
       });

       document.getElementById("inputShipping").addEventListener("input", function () {
           let shippingValue = parseFloat(this.value) || 0;
           document.getElementById("shippingDisplay").textContent = `TK ${shippingValue.toFixed(2)}`;
           updateGrandTotal();
       });

  // Remove item
       productBody.addEventListener("click", function (e) {
            if (e.target.classList.contains("remove-item") || e.target.closest(".remove-item")) {
                e.target.closest("tr").remove();
                updateGrandTotal();
            }
        });

       // Event listeners for paid_amount and full_paid inputs
       let paidAmountInput = document.getElementById("paidAmountInput");
       let fullPaidInput = document.getElementById("fullPaidInput");

       if (paidAmountInput) {
           paidAmountInput.addEventListener("input", function () {
               updateDueAmount();
           });
           paidAmountInput.addEventListener("change", function () {
               updateDueAmount();
           });
       }

       if (fullPaidInput) {
           fullPaidInput.addEventListener("input", function () {
               updateDueAmount();
           });
           fullPaidInput.addEventListener("change", function () {
               updateDueAmount();
           });
       }

       // Initialize grand total and due amount on page load
       setTimeout(function() {
           updateGrandTotal();
           updateDueAmount();
       }, 100);

       // Ensure due_amount is updated before form submission
       let form = document.getElementById("saleReturnUpdateForm");
       if (form) {
           console.log("Form submission handler attached");

           // Also add click handler on submit button for debugging
           let submitButton = form.querySelector("button[type='submit']");
           if (submitButton) {
               submitButton.addEventListener("click", function(e) {
                   console.log("Submit button clicked!");
                   // Don't prevent default - let form submit naturally
               });
           }

           form.addEventListener("submit", function (e) {
               console.log("Form submit event triggered");

               // Check if products array exists
               let productRows = document.querySelectorAll("#productBody tr");
               console.log("Product rows found: " + productRows.length);

               if (productRows.length === 0) {
                   e.preventDefault();
                   alert("Error: No products in the sale return. Please add at least one product.");
                   return false;
               }

               // Convert all quantity inputs to proper numeric values
               document.querySelectorAll(".qty-input").forEach(function (qtyInput) {
                   let qtyValue = parseFloat(qtyInput.value) || 0;
                   qtyValue = Math.max(1, Math.round(qtyValue));
                   qtyInput.value = qtyValue;
               });

               // CRITICAL: Update all subtotals before submission
               document.querySelectorAll("#productBody tr").forEach(function(row) {
                   if (row.querySelector(".qty-input")) {
                       updateSubtotal(row);
                   }
               });

               // Calculate grand total directly from subtotals
               let calculatedTotal = 0;
               document.querySelectorAll(".subtotal").forEach(function (item) {
                   let subtotalText = item.textContent || item.innerText || '0';
                   subtotalText = subtotalText.replace(/[^\d.-]/g, '');
                   calculatedTotal += parseFloat(subtotalText) || 0;
               });

               let discountInput = document.getElementById("inputDiscount");
               let shippingInput = document.getElementById("inputShipping");
               let discount = discountInput ? (parseFloat(discountInput.value) || 0) : 0;
               let shipping = shippingInput ? (parseFloat(shippingInput.value) || 0) : 0;

               calculatedTotal = calculatedTotal - discount + shipping;

               // Update the hidden input
               let grandTotalInput = document.getElementById("grandTotalInput");
               if (grandTotalInput) {
                   grandTotalInput.value = calculatedTotal.toFixed(2);
                   console.log("Grand Total updated to: " + grandTotalInput.value);
               }

               // Update due amount
               updateDueAmount();

               // Validate grand total
               if (calculatedTotal <= 0) {
                   e.preventDefault();
                   alert("Error: Grand Total must be greater than 0. Please check your products and calculations.");
                   return false;
               }

               // Debug: Log form data before submission
               console.log("=== Form Data Before Submission ===");
               let formData = new FormData(form);
               for (let [key, value] of formData.entries()) {
                   if (key.includes('quantity') || key.includes('subtotal') || key === 'grand_total' || key === 'full_paid' || key === 'paid_amount' || key === 'due_amount') {
                       console.log(key + ": " + value);
                   }
               }
               console.log("=== Allowing form to submit ===");

               // Allow form to submit - don't prevent default
           });
       } else {
           console.error("Form element not found!");
       }

});


 </script>


@endsection
