@extends('admin.admin_master')
@section('admin')
    <div class="content">
        <!-- Start Content-->
        <div class="container-xxl">

             <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h2 class="fs-22 fw-semibold m-0">Edit Permission</h2>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0">
                    <a href="{{ route('permission.index') }}" class="btn btn-dark">
                        Back</a>
                </ol>
            </div>
        </div>


            <!-- Form Validation -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Edit Permission</h5>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <form class="row g-3" method="POST" action="{{ route('update.permission', $permission->id) }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="col-md-6">
                                    <label  class="form-label">Permission Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"  name="name" value="{{ $permission->name }}">
                                       @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                     </div>


                                    <div class="col-md-6">
                                        <label  class="form-label">Permission Group</label>
                                        <select name="group_name" class="form-select @error('group_name') is-invalid @enderror">
                                            <option value="" selected >Select Permission Group</option>
                                            <option value="brand" {{ $permission->group_name == 'brand' ? 'selected' : '' }}>Brand</option>
                                            <option value="category" {{ $permission->group_name == 'category' ? 'selected' : '' }}>Category</option>
                                            <option value="product" {{ $permission->group_name == 'product' ? 'selected' : '' }}>Product</option>
                                            <option value="slider" {{ $permission->group_name == 'slider' ? 'selected' : '' }}>Slider</option>
                                            <option value="coupons" {{ $permission->group_name == 'coupons' ? 'selected' : '' }}>Coupons</option>
                                            <option value="shipping" {{ $permission->group_name == 'shipping' ? 'selected' : '' }}>Shipping</option>
                                            <option value="blog" {{ $permission->group_name == 'blog' ? 'selected' : '' }}>Blog</option>
                                            <option value="setting" {{ $permission->group_name == 'setting' ? 'selected' : '' }}>Setting</option>
                                            <option value="returnorder" {{ $permission->group_name == 'returnorder' ? 'selected' : '' }}>Return Order</option>
                                            <option value="review" {{ $permission->group_name == 'review' ? 'selected' : '' }}>Review</option>
                                            <option value="orders" {{ $permission->group_name == 'orders' ? 'selected' : '' }}>Orders</option>
                                            <option value="stock" {{ $permission->group_name == 'stock' ? 'selected' : '' }}>Stock</option>
                                            <option value="reports" {{ $permission->group_name == 'reports' ? 'selected' : '' }}>Reports</option>

                                        </select>
                                        @error('group_name')
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


            </div>
        </div>
    </div>
 <script>
       $(document).ready(function(){
    $('#warehouseemail').on('blur', function() {
        var email = $(this).val();
        if(email.length > 0){
            $.ajax({
                url: '{{ route("check.email") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    email: email
                },
                success: function(response) {
                    if(response.exists){
                        $('#warehouseemail-error').text('This email is already taken');
                    } else {
                        $('#warehouseemail-error').text('');
                    }
                }
            });
        }
    });
});
    </script>
    @endsection

