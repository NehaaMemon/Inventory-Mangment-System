@extends('admin.admin_master')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<style>
    .form-check-label{
        text-transform: capitalize;
    }
</style>

    <div class="content">
        <!-- Start Content-->
        <div class="container-xxl">

            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h2 class="fs-22 fw-semibold m-0">Edit Permission To Role</h2>
                </div>
                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0">
                        <a href="{{ route('all.permission.role') }}" class="btn btn-dark">
                            Back</a>
                    </ol>
                </div>
            </div>


            <!-- Form Validation -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Edit Permission To Role</h5>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <form class="row g-3" method="POST" action="{{ route('update.permission.role', $role->id) }}"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="col-md-6">
                                    <label class="form-label">Role Name</label>
                                    <select class="form-select" disabled>
                                        <option selected>{{ ucfirst($role->name) }}</option>
                                    </select>
                                </div>


                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="formCheck1">
                                    <label class="form-check-label" for="formCheck1">
                                        Permission All
                                    </label>
                                </div>

                                <hr>
                                @foreach ($rolepermission as $group)
                                    <div class="row">
                                        <div class="col-3">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" id="formCheck1">
                                                <label class="form-check-label" for="formCheck1">
                                                    {{ $group->group_name }}
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-9">
                                            @php
                                                $permissions = App\Models\User::getPermissionByGroupName($group->group_name);
                                            @endphp
                                            @foreach ($permissions as $permission)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" name="permission[]"
                                                        value="{{ $permission->id }}" id="formCheck1{{ $permission->id }}"
                                                        {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="formCheck1{{ $permission->id }}">
                                                        {{ $permission->name }}
                                                    </label>
                                                </div>
                                            @endforeach

                                    <br>
                        </div>
                    </div>
                    @endforeach



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
        $('#formCheck1').click(function() {
            if ($(this).prop("checked") == true) {
                $('input[type="checkbox"]').prop('checked', true);
            } else {
                $('input[type="checkbox"]').prop('checked', false);
            }
        });

    </script>
@endsection
