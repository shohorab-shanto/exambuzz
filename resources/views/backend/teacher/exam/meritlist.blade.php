@extends('backend.layouts.master')
@section('title', 'Merit list')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">Merit List for
                        {{ $exam->first()->written->category == '11 to 20 Grade' ? 'Teacher & Lecturer' : $exam->first()->written->category }}
                    </h3>
                </div>
                <a href="{{ route('teacher.written.writtenMeritlistDownload', request()->id) }}" class="white_btn3"
                    target="_blank">Download
                    Meritlist</a>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="white_card card_height_100 mb_30">
                <div class="white_card_body">
                    <form action="{{ route('teacher.written.writtenMeritlist', request()->id) }}">
                        <div class="row mt-2 mb-2">
                            <div class="col-md-5"></div>
                            <div class="col-md-5">
                                <input type="text" name="registration_id" class="form-control" required
                                    value="{{ request()->registration_id ?? '' }}"
                                    placeholder="Enter student registration Id">
                            </div>
                            <div class="col-md-1">
                                <button class="btn btn-primary" type="submit">Search</button>
                            </div>
                            <div class="col-md-1">
                                <a href="{{ route('teacher.written.writtenMeritlist', request()->id) }}"
                                    class="btn btn-primary">reset</a>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Student Name</th>
                                    <th scope="col">Registration Id</th>
                                    <th scope="col">Teacher Name</th>
                                    <th scope="col">Obtained Marks</th>
                                    <th scope="col">Result Status</th>
                                    <th scope="col">Assesment Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($exam as $item)
                                    <tr>
                                        <th scope="row">{{ $loop->iteration }}</th>
                                        <td>{{ $item->user->name ?? 'N/A' }}</td>
                                        <td>{{ $item->user->registration_id ?? 'N/A' }}</td>
                                        <td>{{ $item->teacher->name ?? 'N/A' }}</td>
                                        <td>{{ $item->obtained_mark }}</td>
                                        <td>{{ $item->result_status == 1 ? 'Passed' : 'Failed' }}</td>
                                        <td>{{ $item->updated_at->format('d-m-Y') }}</td>
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
