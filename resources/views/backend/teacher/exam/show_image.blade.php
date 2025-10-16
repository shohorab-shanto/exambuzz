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
                    <div class="row">
                        <div class="col-6 mb-2">
                            <h2>Student Submit Paper</h2>
                        </div>
                        <div class="col-6 mb-2">
                            <h2>Teacher Check Paper</h2>
                        </div>

                        @if(count($question->writtenAnswerQuestionScript) > 0)

                            @foreach($question->writtenAnswerQuestionScript as $image)
                                <div class="col-6 mb-2">
                                    <div class="card">
                                        <div class="card-body" style="text-align: center;">
                                            <a href="{{ asset($image->student_script) }}" target="_blank">
                                                <img
                                                    style="width: auto; max-width: 100%; height: auto; max-height: 300px;"
                                                    src="{{ asset($image->student_script) }}" alt="">
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6 mb-2">
                                    <div class="card">
                                        <div class="card-body" style="text-align: center;">
                                            <a href="{{ asset($image->teacher_script) }}" target="_blank">
                                                <img
                                                    style="width: auto; max-width: 100%; height: auto; max-height: 300px;"
                                                    src="{{ asset($image->teacher_script) }}" alt="">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
