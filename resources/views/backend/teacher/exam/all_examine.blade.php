@extends('backend.layouts.master')
@section('title', 'Merit list')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">All Examinee List
                        @if(count($exam) > 0)
                            {{ $exam->first()->written->category == '11 to 20 Grade' ? 'Teacher & Lecturer' : $exam->first()->written->category }}
                        @endif
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="white_card card_height_100 mb_30">
                <div class="white_card_body">
                    <div class="row mt-2 mb-2">
                        <div class="col-md-5">
{{--                            <a href="{{  route('teacher.written.delete_all_paper', ['id' => $written_id, 'type' => 'student']) }}"--}}
{{--                               class="btn btn-danger mr-10">Delete All Student Paper</a>--}}

                            <a href="{{ route('teacher.written.delete_all_paper', ['id' => $written_id, 'type' => 'student']) }}"
                               class="btn btn-danger mr-10" onclick="return confirm('Are you sure you want to delete all student papers?')">Delete All Student Paper</a>

                            <a href="{{  route('teacher.written.delete_all_paper', ['id' => $written_id, 'type' => 'teacher']) }}"
                               class="btn btn-danger" onclick="return confirm('Are you sure you want to delete all teacher papers?')">Delete All Teacher Paper</a>
                        </div>

                        <div class="col-md-7">
                            <form action="{{ route('teacher.written.all_student_list', request()->id) }}">
                                <div class="row">
                                    <div class="col-md-7">
                                        <input type="text" name="registration_id" class="form-control" required
                                               value="{{ request()->registration_id ?? '' }}"
                                               placeholder="Enter student registration Id">
                                    </div>

                                    <div class="col-md-1 mr-10">
                                        <button class="btn btn-primary" type="submit">Search</button>
                                    </div>

                                    <div class="col-md-1">
                                        <a href="{{ route('teacher.written.all_student_list', request()->id) }}"
                                           class="btn btn-primary">reset</a>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Student Name</th>
                                <th scope="col">Registration Id</th>
                                <th scope="col">Teacher Name</th>
                                <th scope="col">Obtained Marks</th>
                                <th scope="col">Assesment Date</th>
{{--                                <th scope="col">Deleted at</th>--}}
                                <th scope="col">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(count($exam) > 0)
                                @foreach ($exam as $item)
                                    <tr>
                                        <th scope="row">{{ $loop->iteration }}</th>
                                        <td>{{ $item->user->name ?? 'N/A' }}</td>
                                        <td>{{ $item->user->registration_id ?? 'N/A' }}</td>
                                        <td>{{ $item->teacher->name ?? 'N/A' }}</td>
                                        <td>{{ $item->obtained_mark }}</td>
                                        <td>{{ $item->updated_at->format('d-m-Y') }}</td>
{{--                                        <td>{{ $item->deleted_at }}</td>--}}
                                        <td>
                                            @if($item->is_checked != 1)
                                                @if(!$item->deleted_at)
                                                    <a href="{{  route('teacher.written.resubmit_written', ['id' => $item->user_id, 'written_id' => $item->written_id]) }}"
                                                       class="btn btn-sm btn-primary">ReSubmit</a>
                                                @endif
                                            @endif

                                            <a href="{{  route('teacher.written.resubmit_written_show_paper', ['id' => $item->user_id, 'written_id' => $item->written_id]) }}"
                                               class="btn btn-sm btn-info">View Paper</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                        {{ $exam->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
