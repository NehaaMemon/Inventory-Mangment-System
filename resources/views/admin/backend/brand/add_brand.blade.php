@extends('admin.admin_master')
@section('admin')
    <div class="content">
        <!-- Start Content-->
        <div class="container-xxl">

             <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h2 class="fs-22 fw-semibold m-0">Add Brand</h2>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0">
                    <a href="{{ route('all.brand') }}" class="btn btn-dark">
                        Back</a>
                </ol>
            </div>
        </div>


            <!-- Form Validation -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Add Brand</h5>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <form class="row g-3" method="POST" action="{{ route('store.brand') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="col-md-6">
                                    <label for="validationDefault01" class="form-label">Brand name</label>
                                    <input type="text" class="form-control" id="validationDefault01" name="name" >
                                       @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="validationDefault02" class="form-label">Brand Image</label>
                                    <input type="file" class="form-control" id="validationDefault02" name="image" >
                                       @error('image')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                                                    </div>

                                <div class="col-12">
                                    <button class="btn btn-primary" type="submit">Save</button>
                                </div>
                            </form>
                        </div> <!-- end card-body -->
                    </div> <!-- end card-->
                </div> <!-- end col -->

                <!-- Recent Brands Table -->
                <div class="col-12 mt-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recent Brands</h5>
                            <a href="{{ route('all.brand') }}" class="btn btn-sm btn-secondary">See All</a>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Brand Name</th>
                                        <th>Image</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($brand as $key => $item)
                                        <tr>
                                            <td>{{ $key+1 }}</td>
                                            <td>{{ ucfirst($item->name) }}</td>
                                            <td>
                                                <img src="{{ asset($item->image) }}" style="width:70px; height:40px; object-fit:cover;">
                                            </td>
                                            <td>
                                                <a href="{{ route('edit.brand',$item->id) }}" class="btn btn-success btn-sm">Edit</a>
                                                <a href="{{ route('delete.brand',$item->id) }}" class="btn btn-danger btn-sm" id="delete">Delete</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div> <!-- end card-body -->
                    </div> <!-- end card -->
                </div> <!-- end col -->
            </div>
        </div>
    </div>
@endsection
