@extends('admin.admin_master')
@section('admin')

 <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Add Brand</h5>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <form class="row g-3" method="POST" action="{{ route('update.brand',$brand->id) }}"
                            enctype="multipart/form-data">
                                @method('PUT')
                                @csrf
                                <div class="col-md-12">
                                    <label for="validationDefault01" class="form-label">Brand name</label>
                                    <input type="text" class="form-control" id="validationDefault01"
                                    name="name" value="{{ $brand->name }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="validationDefault02" class="form-label">Brand Image</label>
                                    <input type="file" class="form-control" id="validationDefault02"
                                     name="image" >
                                </div>
                                   <div class="col-md-6">
                                    <label for="validationDefault02" class="form-label"></label>
                                    <img id="showImage" src="{{ asset($brand->image) }}"
                                   class="rounded-circle avatar-lg img-thumbnail">
                                </div>

                                <div class="col-12">
                                    <button class="btn btn-primary" type="submit">Save</button>
                                </div>
                            </form>
                        </div> <!-- end card-body -->
                    </div> <!-- end card-->
                </div> <!-- end col -->
 </div>

     <script type="text/javascript">
    $(document).ready(function(){
        $('#validationDefault02').change(function(e){
            var reader = new FileReader();
            reader.onload = function(e){
                $('#showImage').attr('src',e.target.result);
            }
            reader.readAsDataURL(e.target.files['0']);
        })
    })
    </script>

@endsection
