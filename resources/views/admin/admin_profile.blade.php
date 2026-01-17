@extends('admin.admin_master')

@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

     <!-- Start Content-->
        <div class="container-xxl">
            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-semibold m-0">Profile</h4>
                </div>

                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Components</a></li>
                        <li class="breadcrumb-item active">Profile</li>
                    </ol>
                </div>
            </div>

                                <!-- start row -->
<div class="row">
    <div class="col-12">
        <div class="card">

            <div class="card-body">

                <div class="align-items-center">
                    <div class="d-flex align-items-center">
                        <img  src="{{ (!empty($profileData->photo)) ? url('upload/user_images/'
                        .$profileData->photo)
                        : url('upload/no_image.jpg') }}" class="rounded-circle avatar-xxl
                         img-thumbnail float-start" alt="image profile">

                        <div class="overflow-hidden ms-4">
                            <h4 class="m-0 text-dark fs-20">{{ ucfirst($profileData->name) }}</h4>
                            <p class="my-1 text-muted fs-16">{{ $profileData->email }}</p>

                        </div>
                    </div>
                </div>

                    <div class="tab-pane pt-4 active" id="profile_setting" role="tabpanel">
                        <div class="row">

                            <div class="row">
                                <div class="col-lg-6 col-xl-6">
                                    <div class="card border mb-0">

                                        <div class="card-header">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <h4 class="card-title mb-0">Personal Information</h4>
                                                </div><!--end col-->
                                            </div>
                                        </div>
                                            <form action="{{ route('profile.store') }}" method="POST"
                                             enctype="multipart/form-data">
                                                @csrf
                                        <div class="card-body">
                                            <div class="form-group mb-3 row">
                                                <label class="form-label">Name</label>
                                                <div class="col-lg-12 col-xl-12">
                                                    <input class="form-control" type="text" value="{{ $profileData->name }}" name="name">
                                                </div>
                                            </div>

                                                <div class="form-group mb-3 row">
                                                <label class="form-label">Email Address</label>
                                                <div class="col-lg-12 col-xl-12">
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class=
                                                            "mdi mdi-email"></i></span>
                                                        <input type="text" class="form-control"
                                                         value="{{ $profileData->email }}"
                                                        placeholder="Email" aria-describedby=
                                                        "basic-addon1" name="email">
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="form-group mb-3 row">
                                                <label class="form-label">Contact Phone</label>
                                                <div class="col-lg-12 col-xl-12">
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i
                                                            class="mdi mdi-phone-outline"></i></span>
                                                        <input class="form-control" type="text"
                                                         placeholder="Phone" aria-describedby=
                                                        "basic-addon1" value="{{ @$profileData->phone_no }}" name="phone_no">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group mb-3 row">
                                                <label class="form-label">Address</label>
                                                <div class="col-lg-12 col-xl-12">
                                                <textarea class="form-control" value="
                                                {!! @$profileData->address !!}" name="address"></textarea>
                                                </div>
                                            </div>

                                            <div class="form-group mb-3 row">
                                                <label class="form-label">Address</label>
                                                <div class="col-lg-12 col-xl-12">
                                                <input type="file" name="photo" id="photo"
                                                class="form-control">
                                                </div>
                                            </div>

                                            <div class="form-group mb-3 row">

                                                <div class="col-lg-12 col-xl-12">
                                                <img id="showImage" class="rounded-circle
                                                avatar-lg img-thumbnail"
                                                    src="{{ (!empty($profileData->photo)) ?
                                                    url('upload/user_images/'.$profileData->photo)
                                                    : url('upload/no_image.jpg') }}" alt="profile">
                                                </div>
                                            </div>
                                                <button type="submit" class="btn btn-primary">Save
                                                     Changes</button>
                                        </div><!--end card-body-->
                                    </form>
                                    </div>
                                </div>

                                <div class="col-lg-6 col-xl-6">
                                    <div class="card border mb-0">

                                        <div class="card-header">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <h4 class="card-title mb-0">Change Password</h4>
                                                </div><!--end col-->
                                            </div>
                                        </div>

                                        <form method="POST" action="{{ route('Admin.password.update') }}">
                                            @csrf
                                        <div class="card-body mb-0">
                                            <div class="form-group mb-3 row">
                                                <label class="form-label">Old Password</label>
                                                <div class="col-lg-12 col-xl-12">
                                                    <input class="form-control @error('old_password')is-invalid  @enderror" type="password"
                                                    placeholder="Old Password" name="old_password"
                                                    id="old_password">

                                                    @error('old_password')
                                                    <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="form-group mb-3 row">
                                                <label class="form-label">New Password</label>
                                                <div class="col-lg-12 col-xl-12">
                                                     <input class="form-control @error('new_password')is-invalid
                                                     @enderror" type="password"
                                                    placeholder="New Password" name="new_password"
                                                    id="new_password">

                                                    @error('new_password')
                                                    <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="form-group mb-3 row">
                                                <label class="form-label">Confirm Password</label>
                                                <div class="col-lg-12 col-xl-12">
                                                     <input class="form-control @error('confirm_password')is-invalid
                                                     @enderror" type="password"
                                                    placeholder="Confirm Password" name="new_password_confirmation"
                                                    id="confirm_password">

                                                    @error('confirm_password')
                                                    <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-lg-12 col-xl-12">
                                                    <button type="submit" class="btn btn-primary">Change Password</button>
                                                </div>
                                            </div>
                                        </form>
                                        </div><!--end card-body-->
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div> <!-- end education -->


            </div>
        </div>
    </div>
</div>


    <script type="text/javascript">
    $(document).ready(function(){
        $('#photo').change(function(e){
            var reader = new FileReader();
            reader.onload = function(e){
                $('#showImage').attr('src',e.target.result);
            }
            reader.readAsDataURL(e.target.files['0']);
        })
    })
    </script>


@endsection
