@extends('admin.admin_master')
@section('admin')
    <div class="content">
        <!-- Start Content-->
        <div class="container-xxl">

             <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h2 class="fs-22 fw-semibold m-0">Add Role</h2>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0">
                    <a href="{{ route('role.index') }}" class="btn btn-dark">
                        Back</a>
                </ol>
            </div>
        </div>


            <!-- Form Validation -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Add Role</h5>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <form class="row g-3" method="POST" action="{{ route('store.role') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="col-md-6">
                                    <label  class="form-label"> Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"  name="name">
                                       @error('name')
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

