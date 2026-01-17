@extends('admin.admin_master')
@section('admin')
 <div class="content">

<!-- Start Content-->
<div class="container-xxl">

    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">All Product</h4>
        </div>

        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <a href="{{ route('product.create') }}" class="btn btn-secondary">
                    Add Product
                </a>

            </ol>
        </div>
    </div>

    <!-- Datatables  -->
    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-header">
                    {{-- <h5 class="card-title mb-0">All Brand</h5> --}}
                </div><!-- end card header -->

                <div class="card-body">
                    <table id="datatable" class="table table-bordered dt-responsive table-responsive nowrap">
                        <thead>
                        <tr>
                            <th>Id</th>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Warehouse</th>
                            <th>Price</th>
                            <th>Stock </th>
                            <th>Product Qty</th>
                            <th>Status</th>
                            <th>Action Status</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                     @php
                                    $primaryImage = $item->images->first()->image ?? '/uploads/no_image.jpg';
                                @endphp
                                    <img src="{{ asset($primaryImage) }}" style="width: 60px; height:50px;" > </td>
                                <td>{{ ucfirst($item->name) }}</td>
                                <td>{{ $item->warehouse->name }}</td>
                                <td>{{ $item->price }}</td>
                                <td>{{ $item->stock_alert }}</td>

                                <td>@if ($item->product_qty >= 5)
                                    <span class="badge bg-success">{{ $item->product_qty }}</span>
                                    @elseif($item->product_qty <= 4 && $item->product_qty >=1)
                                    <span class="badge bg-warning">{{ $item->product_qty }}</span>
                                    @elseif($item->product_qty ==0)
                                    <span class="badge bg-danger">{{ $item->product_qty }}</span>
                                @endif
                                    </td>
                                 {{-- <td>
                                    @if($item->status == 1)
                                    <span class="badge bg-success">Deliver</span>
                                    @else
                                    <span class="badge bg-danger">Decline</span>
                                    @endif
                                </td> --}}
                                <td>
                                    @if($item->status == "Received")
                                    <span class="badge bg-success">Received</span>
                                    @else
                                    <span class="badge bg-danger">Pending</span>
                                    @endif
                                </td>


                                    <th>
                                    <a title="Edit" href="{{ route('product.edit',$item->id) }}" class="btn btn-warning"><i class="fa-solid fa-pen-to-square"></i></a>
                                     <a title="Detail" href="{{ route('product.show',$item->id) }}" class="btn btn-info"><i class="fa-regular fa-eye"></i></a>


                                 {{-- <form action="{{ route('customers.destroy', $item->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" id="delete"  >Delete</button>
                                </form> --}}
                                <form action="{{ route('product.destroy', $item->id) }}" method="POST" class="delete-form" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button title="Delete" type="button" class="btn btn-danger delete-btn"><i class="fa-regular fa-trash-can"></i></button>
                                    </form>

                                </th>
                            </tr>

                            @endforeach

                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
</div>

@endsection



