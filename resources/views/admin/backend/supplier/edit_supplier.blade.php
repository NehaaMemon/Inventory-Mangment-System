@extends('admin.admin_master')
@section('admin')
    <div class="content">
        <!-- Start Content-->
        <div class="container-xxl">

            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    {{-- <h4 class="fs-18 fw-semibold m-0">Add Brand</h4> --}}
                </div>

                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Supplier</a></li>
                        <li class="breadcrumb-item active">Edit Supplier</li>
                    </ol>
                </div>
            </div>


            <!-- Form Validation -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Edit Supplier</h5>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <form class="row g-3" method="POST" action="{{ route('update.supplier',$supplier->id) }}" >
                                @method('Put')
                                @csrf
                                <div class="col-md-6">
                                    <label  class="form-label">Supplier Name</label>
                                    <input type="text" class="form-control"  name="name" value="{{ $supplier->name }}">
                                     </div>

                                 <div class="col-md-6">
                                    <label  class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email"  value="{{ old('email', $supplier->email) }}" >
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                               


                                 <div class="col-md-6">
                                    <label  class="form-label">Phone No</label>
                                    <input type="text" class="form-control" name="phone"   value="{{ $supplier->phone}}">

                                </div>
                                 <div class="col-md-6">
                                    <label  class="form-label">Address</label>
                                    <input type="text" class="form-control"  name="address"  value="{{ $supplier->address }}" >

                                 </div>

                                <div class="col-12">
                                    <button class="btn btn-primary" type="submit">Save</button>
                                </div>
                            </form>
                        </div> <!-- end card-body -->
                    </div> <!-- end card-->
                </div> <!-- end col -->


            </div>
        </div>
    </div>
@endsection
