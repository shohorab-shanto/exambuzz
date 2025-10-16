@extends('backend.layouts.master')
@section('title', 'Student profile')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">Student Profile</h3>
                    <ol class="breadcrumb page_bradcam mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $company->name }} </a></li>
                        <li class="breadcrumb-item"><a href="{{ route('students') }}">Student</a></li>
                        <li class="breadcrumb-item active">Package History</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">

        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>
                        Package History
                        ||
                        {{ $data->name }}
                        ||
                        {{ $data->registration_id }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Package Name</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Payment Method Identity</th>
                                    <th>Payment Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data->packageHistory as $item)
                                    <tr>
                                        <td>{{ $item->package->name }}</td>
                                        <td>{{ $item->amount }}</td>
                                        <td>{{ $item->payment_method }}</td>
                                        <td>{{ $item->payment_method_identity }}</td>
                                        <td>{{ $item->created_at->format('d F, Y') }}</td>
                                        <td>
                                            <form action="{{ route('studentPackageHistoryDelete',$item->id) }}" method="post" onsubmit="return confirm('Are you sure you want to delete this item?')">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
