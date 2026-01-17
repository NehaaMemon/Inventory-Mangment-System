@extends('admin.admin_master')
@section('admin')
<div class="content">
    <div class="container-xxl">
    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h2 class="fs-22 fw-semibold m-0">Add product</h2>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0">
                    <a href="{{ route('product.index') }}" class="btn btn-dark">
                        Back</a>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card">

                    <div class="card-header">
                        <h5 class="card-title mb-0">Add Product</h5>
                    </div>

                    <div class="card-body">
                        <form class="row g-3" method="POST" action="{{ route('product.store') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="col-xl-8">
                                <div class="card">

                                    <div class="row">

                                        {{-- PRODUCT NAME --}}
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Product Name : <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" placeholder="Enter Name">
                                            @error('name')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- PRODUCT CODE --}}
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Code : <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('code') is-invalid @enderror" name="code" placeholder="Enter Code">
                                            @error('code')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- CATEGORY --}}
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Product Category : <span class="text-danger">*</span></label>
                                            <select class="form-select @error('category_id') is-invalid @enderror" name="category_id">
                                                <option value="">--Select Category--</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}">{{ ucfirst($category->category_name) }}</option>
                                                @endforeach
                                            </select>
                                            @error('category_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- BRAND --}}
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Brand : <span class="text-danger">*</span></label>
                                            <select class="form-select @error('brand_id') is-invalid @enderror" name="brand_id">
                                                <option value="">--Select Brand--</option>
                                                @foreach ($brands as $brand)
                                                    <option value="{{ $brand->id }}">{{ ucfirst($brand->name) }}</option>
                                                @endforeach
                                            </select>
                                            @error('brand_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- PRICE --}}
                                        <div class="col-md-6">
                                            <label class="form-label">Product Price : <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('price') is-invalid @enderror" name="price" placeholder="Enter price">
                                            @error('price')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- DISCOUNT --}}
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Product Discount :</label>
                                            <input type="text" class="form-control @error('discount_price') is-invalid @enderror" name="discount_price" placeholder="Enter discount">
                                            @error('discount_price')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- STOCK ALERT --}}
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Stock Alert :</label>
                                            <input type="number" class="form-control @error('stock_alert') is-invalid @enderror" name="stock_alert" placeholder="Enter Stock Alert">
                                            @error('stock_alert')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- NOTE --}}
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Note</label>
                                            <textarea class="form-control @error('note') is-invalid @enderror" name="note" rows="3" placeholder="Enter Note"></textarea>
                                            @error('note')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                    </div>
                                </div>
                            </div>

                            {{-- RIGHT SIDE CARD --}}
                            <div class="col-xl-4 mb-3">
                                <div class="card">

                                   <label class="form-label">Images :</label>

                        <input
                            class="form-control @error('image') is-invalid @enderror @error('image.*') is-invalid @enderror"
                            type="file"
                            name="image[]"
                            id="multipImg"
                            multiple
                            accept=".jpg,.jpeg,.png">

                        @error('image')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror

                        @error('image.*')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror

                        <div class="row mt-3" id="preview_img"></div>


                                <div class="col-md-12 mb-3 mt-4 text-center">
                                    <h4>Add Stock</h4>
                                </div>
                                </div>
                                {{-- WAREHOUSE --}}
                                <div class="col-md-12">
                                    <label class="form-label">Warehouse : <span class="text-danger">*</span></label>
                                    <select class="form-control form-select @error('warehouse_id') is-invalid @enderror" name="warehouse_id">
                                        <option value="">--Select Warehouse--</option>
                                        @foreach ($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}">{{ ucfirst($warehouse->name) }}</option>
                                        @endforeach
                                    </select>
                                    @error('warehouse_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- SUPPLIER --}}
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Supplier : <span class="text-danger">*</span></label>
                                    <select class="form-control form-select @error('supplier_id') is-invalid @enderror" name="supplier_id">
                                        <option value="">--Select Supplier--</option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ ucfirst($supplier->name) }}</option>
                                        @endforeach
                                    </select>
                                    @error('supplier_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- PRODUCT QTY --}}
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Product Quantity : <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('product_qty') is-invalid @enderror" min="1" name="product_qty" placeholder="Enter Product Quantity">
                                    @error('product_qty')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- STATUS --}}
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Status : <span class="text-danger">*</span></label>
                                    <select class="form-control form-select @error('status') is-invalid @enderror" name="status">
                                        <option value="">--Select Status--</option>
                                        <option value="Received">Received</option>
                                        <option value="Pending">Pending</option>
                                    </select>
                                    @error('status')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                            </div>

                            <div class="col-12">
                                <button class="btn btn-primary" type="submit">Save</button>
                            </div>

                        </form>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>

document.getElementById('multipImg').addEventListener('change', function(e) {

    let preview = document.getElementById('preview_img');
    preview.innerHTML = ""; // clear old previews

    [...e.target.files].forEach((file, index) => {

        if (!file.type.startsWith("image/")) return;

        let reader = new FileReader();
        reader.onload = function(event) {

            let div = document.createElement("div");
            div.classList.add("col-md-3", "mb-2", "position-relative");

            div.innerHTML = `
                <img src="${event.target.result}"
                     class="img-fluid rounded" style="height:140px; object-fit:cover;">
                <button type="button" class="btn btn-danger btn-sm position-absolute"
                        style="top:5px; right:5px;">×</button>
            `;

            // Remove image from preview and input
            div.querySelector("button").onclick = () => {
                div.remove();

                let dt = new DataTransfer();
                [...e.target.files].forEach((f, i) => {
                    if (i !== index) dt.items.add(f);
                });
                e.target.files = dt.files;
            };

            preview.appendChild(div);
        };

        reader.readAsDataURL(file);
    });

});
</script>
@endpush


