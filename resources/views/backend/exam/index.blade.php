@extends('backend.layouts.master')
@section('title', 'All ' . request()->ref . ' ' . request()->type . 'exam list')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">List of
                        {{ request()->child && request()->child == '11 to 20 Grade' ? 'Teacher & Lecturer' : request()->child ?? request()->ref }}{{ ' ' . request()->type }}
                    </h3>
                    <ol class="breadcrumb page_bradcam mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $company->name }} </a></li>
                        <li class="breadcrumb-item"><a
                                href="javascript:void(0);">{{ request()->ref . ' ' . request()->type }}</a></li>
                        @if (request()->child)
                            <li class="breadcrumb-item"><a
                                    href="javascript:void(0);">{{ request()->child && request()->child == '11 to 20 Grade' ? 'Teacher & Lecturer' : request()->child }}
                                    {{ ' ' . request()->type }}
                                    exam</a></li>
                        @endif
                        <li class="breadcrumb-item active">Index</li>
                    </ol>
                </div>
                <a href="{{ route('exam.create', ['ref' => request()->ref, 'type' => 'Preliminary', 'child' => request()->child]) }}"
                   class="white_btn3">Create
                    Preliminary Exam</a>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="white_card card_height_100 mb_30">
                <div class="white_card_body">
                    <form action="{{ route('exam.index') }}">
                        <input type="hidden" name="ref" value="{{ request()->ref }}">
                        <input type="hidden" name="type" value="{{ request()->type }}">
                        @if (isset(request()->child))
                            <input type="hidden" name="child" value="{{ request()->child }}">
                        @endif
                        <div class="row">
                            <div class="col-md-6"></div>
                            <div class="form-group col-md-5">
                                <select name="exam_type" class="form-control" required>
                                    <option value="">select option</option>
                                    <option value="all" {{ request()->exam_type == 'all' ? 'selected' : '' }}>All Exam
                                    </option>
                                    <option value="archive" {{ request()->exam_type == 'archive' ? 'selected' : '' }}>
                                        Archive
                                        Exam
                                    </option>
                                    <option value="live" {{ request()->exam_type == 'live' ? 'selected' : '' }}>Live
                                        Exam
                                    </option>
                                    <option value="upcoming" {{ request()->exam_type == 'upcoming' ? 'selected' : '' }}>
                                        Upcoming Exam
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary">Search</button>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Action</th>
                                    <th scope="col">Exam Date</th>
                                    <th scope="col">Subjects Name</th>
                                    <th scope="col">Topic & Source Name</th>
                                    <th scope="col">Marking System</th>
                                    <th scope="col">Exam Timeline</th>
                                    <th scope="col">Duration(Minutes)</th>
                                    <th scope="col">Total Question</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($exam as $item)
                                    <tr>
                                        <th scope="row">{{ $loop->iteration }}</th>
                                        <td class="d-flex justify-content-around">
                                            <a href="{{ route('exam.mcqQuestion', [$item->id, 'ref' => request()->ref, 'type' => 'Preliminary', 'child' => $item->childcategory, '__s' => $item->subjects->first()->id]) }}"
                                               title="Create or Update Question" class="btn btn-info me-2">
                                                <i class="fas fa-file-signature"></i>
                                            </a>
                                            <a href="{{ route('exam.create', [$item->id, 'ref' => request()->ref, 'type' => 'Preliminary', 'child' => $item->childcategory]) }}"
                                               title="Edit exam" class="btn btn-info me-2">
                                                <i class="far fa-edit"></i>
                                            </a>

                                            <a href="{{ route('exam.writtenMeritlist', $item->id) }}" title="Merit list"
                                               class="btn btn-info me-2">
                                                <i class="fas fa-users"></i>
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
                                            @foreach ($item->topics as $topics)
                                                {{ $topics->topic }} -
                                                {{ $topics->source }}
                                                @if (!$loop->last)
                                                    {{ ',' }}<br>
                                                @endif
                                            @endforeach
                                        </td>
                                        <td>
                                            Positive Makr: {{ $item->per_question_positive_mark }}, <br>
                                            Negative Mark: {{ $item->per_question_negative_mark }}
                                        </td>
                                        <td>
                                            Published Date: {{ $item->published_at->format('d-m-Y h:i A') }}, <br>
                                            Expired Date: {{ $item->expired_at->format('d-m-Y h:i A') }}
                                        </td>
                                        <td>{{ $item->duration / 60 }}</td>
                                        <td>{{ isset($item->questions) ? $item->questions_count : 0 }}</td>
                                        <td>{{ $item->status == 1 ? 'Active' : 'Inactive' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{--                        $exam set pagination --}}
                        {{ $exam->appends(request()->input())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
