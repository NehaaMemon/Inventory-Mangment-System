@extends('admin.admin_master')
@section('admin')
    <div class="content">
        <!-- Start Content-->
        <div class="container-xxl">

             <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h2 class="fs-22 fw-semibold m-0">Edit Admin User</h2>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0">
                    <a href="{{ route('admin.index') }}" class="btn btn-dark">
                        Back</a>
                </ol>
            </div>
        </div>


            <!-- Form Validation -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Edit Admin User</h5>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <form class="row g-3" method="POST" action="{{ route('update.admin', $admin->id) }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="col-md-6">
                                    <label  class="form-label">Admin Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"  name="name" value="{{ old('name', $admin->name) }}">
                                       @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                     </div>

                                 <div class="col-md-6">
                                    <label  class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid
                                    @enderror" name="email" id="warehouseemail" value="{{ old('email', $admin->email) }}">
                                       @error('email')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        <span id="warehouseemail-error" class="text-danger"></span>
                                </div>
                                 <div class="col-md-6">
                                    <label  class="form-label">Password</label>
                                    <input type="password" class="form-control @error('password') is-invalid
                                    @enderror" name="password" value="{{ old('password') }}">
                                        <small class="text-muted">Leave blank if you don't want to change the password</small>
                                       @error('password')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                </div>
                                 <div class="col-md-6">
                                    <label  class="form-label">Role</label>
                                     <select name="role_id" class="form-select @error('role_id') is-invalid @enderror">
                                        <option value="" selected>Select Role</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}" {{ old('role_id', $admin->roles->first()->id ?? null) == $role->id ? 'selected' : '' }}>
                                                {{ ucfirst($role->name) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role_id')
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

   
    @endsection

