@extends('backend.layouts.master')
@section('title', 'Update existing admin')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">Update existing page</h3>
                    <ol class="breadcrumb page_bradcam mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $company->name }} </a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Admin</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">

        <div class="col-lg-12">
            <div class="white_card card_height_100 mb_30">
                <div class="white_card_body">
                    <div class="card-body">
                        <form action="{{ route('admin.update', $admin) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            @method('put')
                            <div class="row mb-3">
                                <div class=" col-md-6">
                                    <label class="form-label" for="inputPassword4">Full Name <span
                                            class="text-danger">*</span> </label>
                                    <input type="text" class="form-control" id="inputPassword4"
                                        placeholder="Enter company or project name" name="name"
                                        value="{{ $admin->name }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="inputEmail4">Phone <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="inputEmail4" placeholder="Phone number"
                                        name="phone" value="{{ $admin->phone }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="inputEmail4">Email <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" class="form-control" id="inputEmail4" placeholder="Email"
                                        name="email" value="{{ $admin->email }}">
                                </div>
                                <div class=" col-md-6">
                                    <label class="form-label" for="inputPassword4">Password (if need to change) </label>
                                    <input type="text" class="form-control" id="inputPassword4"
                                        placeholder="Enter password" name="password">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="inputAddress">Address 
                                </label>
                                <input type="text" class="form-control" id="inputAddress" placeholder="1234 Main St"
                                    name="address" value="{{ $admin->address }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="inputZip">Image  </label>
                                <input type="file" class="form-control" id="inputZip" name="image">
                                @if ($admin->image)
                                    <img src="{{ asset($admin->image) }}" style="height:100px;">
                                @endif
                            </div>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
