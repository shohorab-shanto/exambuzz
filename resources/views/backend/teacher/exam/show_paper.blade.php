@extends('backend.layouts.master')
@section('title', 'Merit list')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">Exam Details
                        @if($exam)
                            {{ $exam->written->category == '11 to 20 Grade' ? 'Teacher & Lecturer' : $exam->written->category }}
                        @endif
                    </h3>
                    <h3>
                        <b>Teacher Name : </b> {{ $exam->teacher->name ?? 'N\A' }} ||
                        <b>Student Name : </b> {{ $exam->user->name ?? 'N/A' }} ||
                        <b>Registration No : </b> {{ $exam->user->registration_id ?? 'N/A' }}
                    </h3>
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
                                <th scope="col">Name</th>
                                <th scope="col">Mark</th>
                                <th scope="col">Obtain</th>
                                <th scope="col">Paper Count</th>
                                <th scope="col">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(count($exam->writtenAnswerQuestion) > 0)
                                @foreach($exam->writtenAnswerQuestion as $question)
                                    <tr>
                                        <th scope="row">{{ $loop->iteration }}</th>
                                        <td>{{ $question->writtenAnswerQuestion->name ?? 'N/A' }}</td>
                                        <td>{{ $question->writtenAnswerQuestion->mark ?? 'N/A' }}</td>
                                        <td>{{ $question->marks ?? 'N/A' }}</td>
                                        <td>{{ count($question->writtenAnswerQuestionScript) ?? 'N/A' }}</td>
                                        <td>
                                            <a href="{{  route('teacher.written.resubmit_written_show_paper_image', ['id' => $exam->user_id, 'written_id' => $exam->written_id, 'question_id' => $question->id]) }}" class="btn btn-sm btn-info">View Paper</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
