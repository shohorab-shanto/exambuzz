@extends('backend.layouts.master')
@section('title', 'All package list')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">List of Package</h3>
                    <ol class="breadcrumb page_bradcam mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $company->name }} </a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Package</a></li>
                        <li class="breadcrumb-item active">Index</li>
                    </ol>
                </div>
                <a href="{{ route('packages.createOrEdit') }}" class="white_btn3">Create Package</a>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mb-3 widget-chart" style="text-align: left;">
                <form action="{{ route('packages.save_active_package') }}" method="POST">
                    @csrf

                    <div class="form-group mb-3">
                        <label class="form-label">Registration ID<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="registration_id" placeholder="Enter Registration ID" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Transaction ID<span class="text-danger"></span></label>
                        <input type="text" class="form-control" name="name" placeholder="Enter Transaction ID">
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label ">Amount<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="amount" placeholder="Enter Amount" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" for="inputAddress">Select Package <span
                                class="text-danger">*</span></label>
                        <select name="package_id" class="form-control" required>
                            <option value="">Select Package</option>
                            @foreach($packages as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}-<b>Price : </b>( {{ $item->amount }} ৳)</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Buy</button>
                </form>
            </div>
        </div>
    </div>
@endsection
