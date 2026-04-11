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
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Warehouse</th>
                        <th>Stock</th>
                    </tr>
                </thead>
            <tbody>
          @foreach($stock as $key => $item)

                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->category ? $item->category->category_name : "N/A" }}</td>
                        <td>{{ $item->warehouse ? $item->warehouse->name : "N/A" }}</td>
                        <td><h4><span class="badge bg-primary">{{ $item->product_qty ?? 0 }}</span></h4></td>

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
     {{-- /// End Card --}}

</div>


@endsection
