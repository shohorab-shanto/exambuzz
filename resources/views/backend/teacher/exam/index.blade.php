@extends('backend.layouts.master')
@section('title', 'All exam list')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">List of
                        {{ request()->child && request()->child=='11 to 20 Grade'?'Teacher & Lecturer':request()->child ?? request()->ref }}{{ ' ' . request()->type }}
                        Exam</h3>
                    <ol class="breadcrumb page_bradcam mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $company->name }} </a></li>
                        <li class="breadcrumb-item"><a
                                href="javascript:void(0);">{{ request()->child && request()->child == '11 to 20 Grade' ? 'Teacher & Lecturer' : request()->child ?? request()->ref }}
                                {{ ' ' . request()->type }}</a></li>
                        <li class="breadcrumb-item active">Index</li>
                    </ol>
                </div>
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
                                <th scope="col">Action</th>
                                <th scope="col">Exam Date</th>
                                <th scope="col">Subjects Name</th>
                                <th scope="col">Exam Timeline</th>
                                <th scope="col">Duration</th>
                                <th scope="col">Examnee</th>
                                <th scope="col">Strategi</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($exam as $item)
                                <tr>
                                    <th scope="row">{{ $loop->iteration }}</th>
                                    <td>
                                        <a href="{{ route('teacher.written.assignPaper', [$item->id, request()->child ? request()->child : request()->ref]) }}"
                                           class="btn btn-info me-2">
                                            <i class="fas fa-file-signature"></i>
                                        </a>
                                        <a href="{{ route('teacher.written.writtenMeritlist', $item->id) }}"
                                           class="btn btn-info me-2">
                                            <i class="fas fa-users"></i>
                                        </a>

                                        <a href="{{ route('teacher.written.all_student_list', $item->id) }}"
                                           class="btn btn-info me-2">
                                            <i class="fas fa-user-circle"></i>
                                        </a>
                                    </td>
                                    <td>{{ $item->published_at->format('d F Y') }}</td>
                                    <td>
                                        @foreach ($item->subjects as $subject)
                                            {{ $subject->name }}
                                            @if (!$loop->last)
                                                {{ ',' }}<br>
                                            @endif
                                        @endforeach
                                    </td>
                                    <td>
                                        {{ $item->published_at->format('Y-m-d') }} <br> <b>to</b> <br>
                                        {{ $item->expired_at->format('Y-m-d') }}
                                    </td>
                                    <td>{{ $item->duration / 60 }} M.</td>
                                    <td>{{ $item->answer_count }}</td>
                                    <td>
                                            <span title="Total assigned paper"
                                                  class="text-success fw-bolder">{{ getPaperTeacher($item->id)['assigned'] }}</span>/
                                        <span class="text-primary fw-bolder"
                                              title="Total submitted paper">{{ $item->answer_count }}</span> / <span
                                            title="Total assigned teacher"
                                            class="text-warning fw-bolder">{{ getPaperTeacher($item->id)['assigned_teacher'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {{ $exam->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
