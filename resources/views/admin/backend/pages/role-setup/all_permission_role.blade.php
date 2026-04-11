
@extends('admin.admin_master')
@section('admin')

    <div class="content">

        <!-- Start Content-->
        <div class="container-xxl">

            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-semibold m-0">All Permission To Roles</h4>
                </div>

                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0">
                        <a href="{{ route('add.permission.role') }}" class="btn btn-secondary">
                            Add Permission To Role
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
                                    <th>Role Name</th>
                                    <th>Permission Name</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach ($roles as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ ucfirst($item->name) }}</td>
                                        <td>
                                            @foreach ($item->permissions as $permission)
                                                <span class="badge bg-primary">{{ ucfirst($permission->name) }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            <a href="{{ route('edit.role',$item->id) }}" class="btn btn-success">Edit</a>
                                            <a href="{{ route('delete.role',$item->id) }}" class="btn btn-danger" id="delete">Delete</a>
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
