@extends('admin.admin_master')
@section('admin')
    <div class="content">
        <div class="container-xxl">
            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-semibold m-0">Product Details</h4>
                </div>
                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0">
                        <a href="{{ route('product.index') }}" class="btn btn-secondary">
                            Back to Product List
                        </a>
                    </ol>
                </div>
            </div>
            <hr>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5">
                      <h5 class="card-title mb-4">Product Information</h5>

                            <div class="d-flex flex-wrap gap-3">
                                @forelse ($product->images as $image)
                                    <div class="img-box">
                                        <img src="{{ asset($image->image) }}" alt="Product Image">
                                    </div>
                                @empty
                                    <p class="text-danger">No images available for this product.</p>
                                @endforelse
                            </div>

                        </div>

                        <div class="col-md-7">
                            <h5 class="card-title mb-4">Product Details</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><strong>Name:</strong> {{ ucfirst($product->name) }}</li>
                                <li class="list-group-item"><strong>Code:</strong> {{ $product->code }}</li>
                                <li class="list-group-item"><strong>Category:</strong> {{ ucfirst($product->category->category_name) }}</li>
                                <li class="list-group-item"><strong>Brand:</strong> {{ $product->brand->name }}</li>
                                <li class="list-group-item"><strong>Supplier:</strong> {{ $product->supplier->name }}</li>
                                <li class="list-group-item"><strong>Warehouse:</strong> {{ $product->warehouse->name }}</li>
                                <li class="list-group-item"><strong>Note:</strong> {{ $product->note }}</li>
                                <li class="list-group-item"><strong>Product Quantity:</strong> {{ $product->product_qty }}</li>
                                <li class="list-group-item"><strong>Price:</strong> {{ $product->price }}</li>
                                <li class="list-group-item"><strong>Discount Price:</strong> {{ $product->discount_price }}</li>
                                <li class="list-group-item"><strong>Stock Alert:</strong> {{ $product->stock_alert }}</li>
                                <li class="list-group-item"><strong>Status:</strong>
                                    @if($product->status == "Received")
                                    <span class="badge bg-success">Received</span>
                                    @else
                                    <span class="badge bg-danger">Pending</span>
                                    @endif
                                </li>
                                <li class="list-group-item"><strong>Created At:</strong>
                                    {{ $product->created_at->format('d M Y, h:i A') }}</li>
                            </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

