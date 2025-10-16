@extends('backend.layouts.master')
@section('title', 'All teacher list')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">List of teacher</h3>
                    <ol class="breadcrumb page_bradcam mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $company->name }} </a></li>
                        <li class="breadcrumb-item"><a href="{{ route('teacher.index') }}">Teacher</a></li>
                        <li class="breadcrumb-item active">Index</li>
                    </ol>
                </div>
                <a href="{{ route('teacher.createOrEdit') }}" class="white_btn3">Create Teacher</a>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="white_card card_height_100 mb_30">
                <div class="white_card_body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Phone</th>
                                    <th scope="col">Permission</th>
                                    <th scope="col">Total Withdrawal</th>
                                    <th scope="col">Total Earning</th>
                                    <th scope="col">Pending Withdrawal</th>
                                    <th scope="col">Total Assesment</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $item)
                                    <tr>
                                        <th scope="row">{{ $loop->iteration }}</th>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->phone }}</td>
                                        <td>{{ $item->permission ?? '' }}</td>
                                        <td>{{ !is_null($item->wallet) ? number_format($item->wallet->withdraw, 2) : 0 }}
                                        </td>
                                        <td>{{ !is_null($item->wallet) ? number_format($item->wallet->amount + $item->wallet->withdraw, 2) : 0 }}
                                        </td>
                                        <td>{{ !is_null($item->wallet) && !is_null($item->wallet->teacherWalletHistory) ? $item->wallet->teacherWalletHistory->where('status', 'Pending')->sum('amount') : 0 }}
                                        </td>
                                        <td>{{ number_format($item->assesment_count, 0) }}</td>
                                        <td class="d-flex justify-content-around">
                                            <a href="{{ route('teacher.createOrEdit', $item->id) }}"
                                                class="btn btn-info me-2">
                                                <i class="far fa-edit"></i>
                                            </a>

                                            <a href="{{ route('teacher.show', $item->id) }}" class="btn btn-success me-2">
                                                <i class="far fa-eye"></i>
                                            </a>
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
