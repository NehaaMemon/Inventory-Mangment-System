@extends('admin.admin_master')
@section('admin')

<div class="page-content m-2">
    <div class="container">

        @include('admin.backend.report.report_layout.report_top')
    </div>
     {{-- /// end Container  --}}

     <div class="card">

        <nav class="navbar navbar-expand-lg bg-dark">
            <div class="container-fluid">
                <div class="collapse navbar-collapse" id="navbarNav">

                    @include('admin.backend.report.report_layout.report_menu')
</div>

            </div>
        </nav>

    <div class="card-body">
        <div class="table-responsive">
            <div id="example_wrapper" class="dataTables_wrapper dt-bootstrap5">
    <div class="row">
        <div class="col-sm-12">
            <table id="example" class="table table-striped table-bordered dataTable" style="width: 100%;" role="grid" aria-describedby="example_info">
                <thead>
                    <tr role="row">
                        <th>ID</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Warehouse</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Status</th>
                        <th>Grand Total</th>
                    </tr>
                </thead>
            <tbody>
          @foreach($saleReturn as $saleReturns)
                @foreach ($saleReturns->saleReturnItems as $item)
                    <tr>
                        <td>{{ $saleReturns->id }}</td>
                        <td>{{ $saleReturns->date }}</td>
                        <td>{{ $saleReturns->customer ? $saleReturns->customer->name : "N/A" }}</td>
                        <td>{{ $saleReturns->warehouse ? $saleReturns->warehouse->name : "N/A" }}</td>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity ?? 0 }}</td>
                        <td>{{ $item->net_unit_cost ?? 0 }}</td>
                        <td>{{ $saleReturns->status }}</td>
                        <td>{{ $saleReturns->grand_total ?? 0 }}</td>
                    </tr>
                @endforeach
          @endforeach
            </tbody>

            </table>

        </div>

    </div>

</div>

        </div>
    </div>





     </div>
     {{-- /// End Card --}}

</div>


@endsection
