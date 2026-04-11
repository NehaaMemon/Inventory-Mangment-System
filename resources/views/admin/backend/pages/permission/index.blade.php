
@extends('admin.admin_master')
@section('admin')

    <div class="content">

        <!-- Start Content-->
        <div class="container-xxl">

            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-semibold m-0">All Permissions</h4>
                </div>

                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0">
                        <a href="{{ route('add.permission') }}" class="btn btn-secondary">
                            Add Permission
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
                                    <th>Permission Name</th>
                                    <th>Permission Group</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach ($permissions as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ ucfirst($item->name) }}</td>
                                        <td>{{ $item->group_name }}</td>

                                            <td>
                                            <a href="{{ route('edit.permission',$item->id) }}" class="btn btn-success">Edit</a>
                                            <a href="{{ route('delete.permission',$item->id) }}" class="btn btn-danger" id="delete">Delete</a>
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
