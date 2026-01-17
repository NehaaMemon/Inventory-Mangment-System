@extends('admin.admin_master')
@section('admin')
<div class="content">

<!-- Start Content-->
<div class="container-xxl">

    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">All Category</h4>
        </div>

        <div class="text-end">
            <ol class="breadcrumb m-0 py-0">
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                    data-bs-target="#standard-modal">
                    Add Category
                </button>
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
                                <th>Category Name</th>
                                <th>Category Slug</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($category as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ ucfirst($item->category_name) }}</td>
                                    <td>{{ $item->category_slug }}</td>

                                    <th>
                                            <!-- Remove data-bs-toggle and data-bs-target -->
                                    <button type="button" class="btn btn-success" id="{{ $item->id }}" onclick="categoryEdit(this.id)">
                                        Edit
                                    </button>

                                    
                                        <form action="{{ route('category.destroy', $item->id) }}" method="POST"
                                            class="delete-form" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger delete-btn">Delete</button>
                                        </form>

                                    </th>
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

    {{-- Category create Modal --}}

    <div class="modal fade" id="standard-modal" tabindex="-1" aria-labelledby="standard-modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="standard-modalLabel">Add Category </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('category.store') }}">
                        @csrf
                        <div class="form-group col-md-12">
                            <label for="category_name" class="form-label">Product Category Name</label>
                            <input type="text" class="form-control" id="category_name" name="category_name" required>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


   
@endsection
@push('scripts')
        <script>

    function categoryEdit(id){
    $.ajax({
        url: '/category/' + id + '/edit', // ya jo URL ab sahi kaam kar raha hai
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            console.log(data);
            
            $('#cat').val(data.category_name);
            $('#cat_id').val(data.id);

            // set form action dynamically
            $('#editCategoryForm').attr('action', '/category/' + id);

           $('#editcategory').modal('show'); 
        },
        error: function(xhr){
            console.log('AJAX error:', xhr.responseText);
        }
    });
}


</script>
@endpush
    
