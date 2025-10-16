@extends('backend.layouts.master')
@section('title', 'All student list')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">List of student</h3>
                    <ol class="breadcrumb page_bradcam mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $company->name }} </a></li>
                        <li class="breadcrumb-item"><a href="{{ route('students') }}">student</a></li>
                        <li class="breadcrumb-item active">Index</li>
                    </ol>
                </div>
                {{-- <a href="{{ route('student.createOrEdit') }}" class="white_btn3">Create student</a> --}}
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="white_card card_height_100 mb_30">
                <div class="white_card_body">
                    <div class="mt-2 mb-2">
                        <form action="{{ route('students') }}">
                            <div class="row">
                                <div class="col-md-5">
                                    <select name="package_id" class=" form-control " data-placeholder="Select package">
                                        <option value="">Select package</option>
                                        <option value="no" {{ request()->package_id == 'no' ? 'selected' : '' }}>No
                                            purchase package</option>
                                        @foreach ($packages as $item)
                                            <option value="{{ $item->id }}"
                                                {{ request()->package_id == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <input type="text" name="registration_id" class="form-control"
                                        placeholder="Registration Id">
                                </div>
                                <div class="col-md-1">
                                    <button class="btn btn-primary" type="submit">Search</button>
                                </div>
                                <div class="col-md-1">
                                    <a href="{{ route('students') }}" class="btn btn-primary">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Phone</th>
                                    <th scope="col">Registration Id</th>
                                    <th scope="col">Purchased Package</th>
                                    <th scope="col">Purchase Amount</th>
                                    <th scope="col">Preliminary Exam</th>
                                    <th scope="col">Written Exam</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($student as $item)
                                    <tr>
                                        <th scope="row">{{ $loop->iteration }}</th>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->phone }}</td>
                                        <td>{{ $item->registration_id }}</td>
                                        <td>{{ $item->package_history_count }}</td>
                                        <td>{{ $item->packageHistory->sum('amount') }}</td>
                                        <td>{{ liveExamCount($item->id)['p_count'] }}</td>
                                        <td>{{ liveExamCount($item->id)['w_count'] }}</td>
                                        <td>{{ $item->status == 1 ? 'Active' : 'Inactive' }}</td>

                                        <td class="d-flex justify-content-around">
                                            <a title="{{ $item->status == 0 ? 'Make student status active' : 'Make student status inactive' }}"
                                                href="{{ route('changeStudentStatus', $item->id) }}"
                                                class="btn {{ $item->status == 0 ? 'btn-info' : 'btn-danger' }} me-2">
                                                @if ($item->status == 0)
                                                    <i class="fas fa-thumbs-up"></i>
                                                @else
                                                    <i class="fas fa-thumbs-down"></i>
                                                @endif
                                            </a>

                                            <a href="{{ route('showStudentDetails', $item->id) }}"
                                                class="btn btn-success me-2">
                                                <i class="far fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-2">
                            {{ $student->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
