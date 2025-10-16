@extends('backend.layouts.master')
@section('title', (request()->id ? 'Update' : 'Create new') . ' teacher')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">{{ request()->id ? 'Update' : 'Create new' }} teacher</h3>
                    <ol class="breadcrumb page_bradcam mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $company->name }} </a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Teacher</a></li>
                        <li class="breadcrumb-item active">{{ request()->id ? 'Update' : 'Create' }}</li>
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
                        <form
                            action="{{ request()->id ? route('teacher.storeOrUpdate', request()->id) : route('teacher.storeOrUpdate') }}"
                            method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row mb-3">
                                <div class=" col-md-6">
                                    <label class="form-label" for="inputPassword4">Full Name <span
                                            class="text-danger">*</span> </label>
                                    <input type="text" class="form-control" id="inputPassword4"
                                        placeholder="Enter company or project name" name="name"
                                        value="{{ $data->name ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="inputEmail4">Phone <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="inputEmail4" placeholder="Phone number"
                                        name="phone" value="{{ $data->phone ?? '' }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="inputEmail4">Email <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" class="form-control" id="inputEmail4" placeholder="Email"
                                        name="email" value="{{ $data->email ?? '' }}">
                                </div>
                                <div class=" col-md-6">
                                    <label class="form-label" for="inputPassword4">Password
                                        {{ request()->id ? '(if need to change)' : '' }}</label>
                                    <input type="text" class="form-control" id="inputPassword4"
                                        placeholder="Enter password" name="password">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="inputAddress">Address
                                </label>
                                <input type="text" class="form-control" id="inputAddress" placeholder="1234 Main St"
                                    name="address" value="{{ $data->address ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="inputAddress">About
                                </label>
                                <textarea name="about" class="form-control" rows="5">{{ $data->about ?? '' }}</textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="inputZip">Image
                                        </label>
                                        <input type="file" class="form-control" id="inputZip" name="image">
                                        @if ($data)
                                            <img src="{{ asset($data->image) }}" style="height:200px;">
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="inputPassword4">Per student paper amount<span
                                            class="text-danger">*</span> </label>
                                    <input type="number" class="form-control" id="inputPassword4"
                                        placeholder="Enter amount" name="amount" value="{{ $data->amount ?? '' }}">
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="white_card_header">
                                        <div class="box_header m-0">
                                            <div class="main-title">
                                                <h3 class="m-0">Teacher Permission</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="white_card_body">
                                        <div class="card-body">
                                            <div class="mb-3 form-check">
                                                <input type="checkbox" name="permission[]" class="form-check-input"
                                                    id="bcs" value="BCS"
                                                    {{ $data ? (in_array('BCS', explode(',', $data->permission)) ? 'checked' : '') : '' }}>
                                                <label class="form-label form-check-label" for="bcs">BCS</label>
                                            </div>
                                            <div class="mb-3 form-check">
                                                <input type="checkbox" name="permission[]" class="form-check-input"
                                                    id="Bank" value="Bank"
                                                    {{ $data ? (in_array('Bank', explode(',', $data->permission)) ? 'checked' : '') : '' }}>
                                                <label class="form-label form-check-label" for="Bank">Bank</label>
                                            </div>
                                            <div class="mb-3 form-check">
                                                <input type="checkbox" name="permission[]" class="form-check-input"
                                                    id="Job Solution" value="Job Solution"
                                                    {{ $data ? (in_array('Job Solution', explode(',', $data->permission)) ? 'checked' : '') : '' }}>
                                                <label class="form-label form-check-label" for="Job Solution">Job
                                                    Solution</label>
                                            </div>
                                            <div class="mb-3 form-check">
                                                <input type="checkbox" name="permission[]" class="form-check-input"
                                                    id="11 to 20 Grade" value="11 to 20 Grade"
                                                    {{ $data ? (in_array('11 to 20 Grade', explode(',', $data->permission)) ? 'checked' : '') : '' }}>
                                                <label class="form-label form-check-label" for="11 to 20 Grade">11 to 20
                                                    Grade</label>
                                            </div>
                                            <div class="mb-3 form-check">
                                                <input type="checkbox" name="permission[]" class="form-check-input"
                                                    id="Weekly" value="Weekly"
                                                    {{ $data ? (in_array('Weekly', explode(',', $data->permission)) ? 'checked' : '') : '' }}>
                                                <label class="form-label form-check-label" for="Weekly">Weekly</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="white_card_header">
                                        <div class="box_header m-0">
                                            <div class="main-title">
                                                <h3 class="m-0">Teacher Status</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="white_card_body">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="status"
                                                    id="active" value="1"
                                                    {{ $data && $data->status == 1 ? 'checked' : '' }}>
                                                <label class="form-label form-check-label" for="active">
                                                    Active
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="status"
                                                    id="inactive" value="0"
                                                    {{ $data && $data->status == 0 ? 'checked' : '' }}>
                                                <label class="form-label form-check-label" for="inactive">
                                                    Inactive
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit"
                                class="btn btn-primary">{{ request()->id ? 'Update' : 'Create' }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
