@extends('admin.admin_master')
@section('admin')
    <div class="content">
        <!-- Start Content-->
        <div class="container-xxl">

             <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h2 class="fs-22 fw-semibold m-0">Add WareHouse</h2>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0">
                    <a href="{{ route('all.warehouse') }}" class="btn btn-dark">
                        Back</a>
                </ol>
            </div>
        </div>


            <!-- Form Validation -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Add Warehouse</h5>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <form class="row g-3" method="POST" action="{{ route('store.warehouse') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="col-md-6">
                                    <label  class="form-label">Warehouse Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"  name="name">
                                       @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                     </div>

                                 <div class="col-md-6">
                                    <label  class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid
                                    @enderror" name="email" id="warehouseemail">
                                       @error('email')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        <span id="warehouseemail-error" class="text-danger"></span>
                                </div>
                                 <div class="col-md-6">
                                    <label  class="form-label">Phone No</label>
                                    <input type="text" class="form-control @error('phone') is-invalid
                                    @enderror" name="phone" >
                                       @error('phone')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                </div>
                                 <div class="col-md-6">
                                    <label  class="form-label">City</label>
                                    <input type="text" class="form-control @error('city') is-invalid
                                    @enderror"  name="city"  >
                                       @error('city')
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
                            <h5 class="card-title mb-0">Recent Warehouses</h5>
                            <a href="{{ route('all.warehouse') }}" class="btn btn-sm btn-secondary">See All</a>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Warehouse Name</th>
                                        <th>Email</th>
                                        <th>Phone no</th>
                                        <th>City</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($warehouse as  $item)
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
                        </div> <!-- end card-body -->
                    </div> <!-- end card -->
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

