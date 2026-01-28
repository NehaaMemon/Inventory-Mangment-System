@extends('admin.admin_master')
@section('admin')
 <div class="content">

<!-- Start Content-->
<div class="container-xxl">

    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">All Sale Due</h4>
        </div>

        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">

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
                <th>WareHouse</th>
                <th>Customer Name</th>
                <th>Due Amount</th>
                <th>Full Payment</th>

            </tr>
            </thead>
            <tbody>
                @foreach ($sale as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->warehouse->name }}</td>
                    <td>{{ $item->customer->name }}</td>
                    <td><h4><span class="badge bg-warning ">$ {{ $item->due_amount }}</span></h4></td>


                        <th>
                        <a title="Payment" href="{{ route('sale.edit',$item->id) }}"
                            class="btn btn-success">Pay now
                               </a>


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



