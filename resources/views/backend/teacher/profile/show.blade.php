@extends('backend.layouts.master')
@section('title', 'Teacher profile')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">Teacher Profile</h3>
                    <ol class="breadcrumb page_bradcam mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $company->name }} </a></li>
                        <li class="breadcrumb-item"><a href="{{ route('teacher.index') }}">Teacher</a></li>
                        <li class="breadcrumb-item active">Profile</li>
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
                        <div class="row">
                            <div class="col-md-6 mt-3">
                                <h6>Name</h6>
                                <p>{{ $data->name }}</p>
                            </div>
                            <div class="col-md-6 mt-3">
                                <h6>Phone</h6>
                                <p>{{ $data->phone }}</p>
                            </div>
                            <div class="col-md-6 mt-3">
                                <h6>Email</h6>
                                <p>{{ $data->email }}</p>
                            </div>
                            <div class="col-md-6 mt-3">
                                <h6>Registration ID</h6>
                                <p>{{ $data->registration_id }}</p>
                            </div>
                            <div class="col-md-6 mt-3">
                                <h6>Amount</h6>
                                <p>{{ $data->amount }} tk per student</p>
                            </div>
                            <div class="col-md-6 mt-3">
                                <h6>Status</h6>
                                <p>{{ $data->status == 1 ? 'Active' : 'Inactive' }}</p>
                            </div>
                            <div class="col-md-12 mt-3">
                                <h6>Address</h6>
                                <p>{{ $data->address }}</p>
                            </div>
                            <div class="col-md-12 mt-3">
                                <h6>About</h6>
                                <p>{{ $data->about }}</p>
                            </div>
                            <div class="col-md-6 mt-3">
                                <h6>Permission</h6>
                                <p>{{ $data->permission }}</p>
                            </div>
                            <div class="col-md-6 mt-3">
                                <h6>Joined</h6>
                                <p>{{ $data->created_at->format('d F, Y') }}</p>
                            </div>
                            <div class="col-md-6 mt-3">
                                <h6>Total Withdrawal</h6>
                                <p>{{ !is_null($data->wallet) ? number_format($data->wallet->withdraw, 2) : 0 }} BDT</p>
                            </div>
                            <div class="col-md-6 mt-3">
                                <h6>Total Earning</h6>
                                <p>{{ !is_null($data->wallet) ? number_format($data->wallet->amount + $data->wallet->withdraw, 2) : 0 }}
                                    BDT</p>
                            </div>
                            <div class="col-md-6 mt-3">
                                <h6>Pending Withdrawal</h6>
                                <p>{{ !is_null($data->wallet) && !is_null($data->wallet->teacherWalletHistory) ? $data->wallet->teacherWalletHistory->where('status', 'Pending')->sum('amount') : 0 }}
                                    BDT</p>
                            </div>
                            <div class="col-md-6 mt-3">
                                <h6>Total Assesment</h6>
                                <p>{{ number_format($data->assesment_count, 0) }} Student Exam Papers</p>
                            </div>
                            <div class="col-md-6 mt-3">
                                <h6>Image</h6>
                                <img src="{{ asset($data->image) }}" style="height:150px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
