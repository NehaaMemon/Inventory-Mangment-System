@extends('admin.admin_master')
@section('admin')

    <div class="content">

        <!-- Start Content-->
        <div class="container-xxl">

            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-semibold m-0">All Warehouses</h4>
                </div>

                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0">
                        <a href="{{ route('add.warehouse') }}" class="btn btn-secondary">
                            Add Warehouse
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
                                    <th>Warehouse Name</th>
                                    <th>Email</th>
                                    <th>Phone No</th>
                                    <th>City</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach ($warehouse as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ ucfirst($item->name) }}</td>                                       
                                        <td>{{ $item->email }}</td>
                                         <td>{{ $item->phone }}</td>
                                           <td>{{ ucfirst($item->city) }}</td>
                                            <td>
                                            <a href="{{ route('edit.warehouse',$item->id) }}" class="btn btn-success">Edit</a>
                                            <a href="{{ route('delete.warehouse',$item->id) }}" class="btn btn-danger" id="delete">Delete</a>
                                        </td>
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
