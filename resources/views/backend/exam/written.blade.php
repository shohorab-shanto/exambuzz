@extends('backend.layouts.master')
@section('title', 'All ' . request()->ref . ' ' . request()->type . 'exam list')
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
                                href="javascript:void(0);">{{ request()->ref . ' ' . request()->type }}</a></li>
                        <li class="breadcrumb-item active">Index</li>
                    </ol>
                </div>
                <a href="{{ route('exam.writtenCreate', ['ref' => request()->ref, 'type' => 'Written', 'child' => request()->child]) }}"
                   class="white_btn3">Create
                    Written Exam</a>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="white_card card_height_100 mb_30">
                <div class="white_card_body">
                    <form action="{{ route('exam.written') }}">
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
                                    <th scope="col">Name</th>
                                    <th scope="col">Exam Name</th>
                                    <th scope="col">Subjects Name</th>
                                    <th scope="col">Topic & Source Name</th>
                                    {{-- <th scope="col">Marking System</th> --}}
                                    <th scope="col">Exam Timeline</th>
                                    <th scope="col">Duration(Minutes)</th>
                                    <th scope="col">Question & Answer</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($exam as $item)
                                    <tr>
                                        <th scope="row">{{ $loop->iteration }}</th>

                                        <td class="d-flex justify-content-around">
                                            <a href="{{ route('exam.writtenQuestion', [$item->id, 'ref' => request()->ref, 'type' => 'Written', 'child' => $item->childcategory]) }}"
                                               class="btn btn-info me-2">
                                                <i class="fas fa-file-signature"></i>
                                            </a>
                                            <a href="{{ route('exam.writtenCreate', [$item->id, 'ref' => request()->ref, 'type' => 'Written', 'child' => $item->childcategory]) }}"
                                               class="btn btn-info me-2">
                                                <i class="far fa-edit"></i>
                                            </a>

                                            {{-- <form action="{{ route('page.delete',$page) }}" method="post">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form> --}}
                                        </td>
                                        <th scope="row">{{ $item->name }}</th>
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
                                        {{-- <td>
                                            000
                                        </td> --}}
                                        <td>
                                            Published Date: {{ $item->published_at->format('Y-m-d H:i:s A') }}, <br>
                                            Expired Date: {{ $item->expired_at->format('Y-m-d H:i:s A') }}
                                        </td>
                                        <td>{{ $item->duration / 60 }}</td>
                                        <td>
                                            @if ($item->question)
                                                <a target="_blank" class="btn btn-sm btn-outline-info"
                                                   href="{{ asset($item->question) }}">View Question</a>
                                            @else
                                                No question set yet
                                            @endif
                                            <br>
                                            <br>
                                            @if ($item->answer)
                                                <a target="_blank" class="btn btn-sm btn-outline-info"
                                                   href="{{ asset($item->answer) }}">View Answer</a>
                                            @else
                                                No answer set yet
                                            @endif
                                        </td>
                                        <td>{{ $item->status == 1 ? 'Active' : 'Inactive' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{ $exam->appends(request()->input())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
