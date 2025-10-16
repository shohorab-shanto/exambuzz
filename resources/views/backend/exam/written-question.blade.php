@extends('backend.layouts.master')
@section('title', 'Manage question for ' . $exam->name)
@section('css')
    <style>
        .note-editor.note-frame {
            border: none;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">Manage question for "{{ $exam->name }}"</h3>
                    <ol class="breadcrumb page_bradcam mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $company->name }} </a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Manage Question</a></li>
                        <li class="breadcrumb-item active">{{ request()->written_id ? 'Update' : 'Create' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">

        <div class="col-lg-12">
            <div class="white_card card_height_100 mb_30">
                <div class="white_card_body">
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <b>
                                <i>{{ $exam->category }}</i> > <i>{{ $exam->subcategory }}</i>
                                @if ($exam->childcategory)
                                    > {{ $exam->childcategory }}
                                @endif
                            </b>
                            <br>
                            @php
                                $subjects = DB::table('subjects')
                                    ->whereIn('id', explode(',', $exam->subject_id))
                                    ->get();

                                $topic = DB::table('topic_sources')
                                    ->whereIn('id', explode(',', $exam->topic_id))
                                    ->get();
                            @endphp
                            <b>Subjects:</b>
                            <div class="ms-5">
                                <ul>
                                    @foreach ($subjects as $subject)
                                        <li style="list-style-type: circle;color: rgb(17, 0, 255);">{{ $subject->name }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <br>
                            <b>Topics & Sources:</b>
                            <div class="ms-5">
                                <ul>
                                    @foreach ($topic as $top)
                                        <li style="list-style-type: dot;color: rgb(17, 0, 255);">{{ $top->topic }} -
                                            ({{ $top->source }})
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <br>
                        </div>
                        @csrf
                        <h2 class="text-center"><u>Create written question</u></h2>
                        <br>
                        <br>
                        <br>
                        @if (isset($subjects))
                            @foreach ($subjects as $q_subject)
                                @php
                                    $present_question = App\Models\WrittenQuestion::where('written_id', request()->written_id)
                                        ->where('subject_id', $q_subject->id)
                                        ->get();
                                @endphp
                                <div class="row justify-content-center mb-5">
                                    <div class="col-lg-10">
                                        <div class="card_box box_shadow position-relative">
                                            <div class="white_box_tittle" style="padding: 20px;">
                                                <div class="d-flex justify-content-between">
                                                    <h4>{{ $q_subject->name }}</h4>

                                                </div>
                                            </div>

                                            <form action="{{ route('exam.createOrUpdateWrittenQuestion', $exam->id) }}"
                                                method="post" enctype="multipart/form-data">
                                                @csrf

                                                <div class="box_body">
                                                    {{-- existing question here to update --}}
                                                    @foreach ($present_question as $question)
                                                        @php
                                                            $present_serial = $loop->iteration;
                                                        @endphp
                                                        <div class="mb-2">
                                                            <div class="alert alert-success" style="margin-bottom: 1px;">
                                                                <div class="d-flex justify-content-between">
                                                                    <h4>Question Number #
                                                                        <span>{{ $loop->iteration }}</span>
                                                                    </h4>
                                                                    <div class="d-flex justify-content-end">
                                                                        <i class="fas fa-minus-circle fa-lg"
                                                                            style="padding-top: 8px;display: none;cursor: pointer;"
                                                                            onclick="closeQuestion(this)"
                                                                            title="Collapse"></i>
                                                                        <i class="fas fa-plus-circle fa-lg"
                                                                            style="padding-top: 8px;cursor: pointer;"
                                                                            onclick="openQuestion(this)" title="Expand"></i>
                                                                        <i class="fas fa-times-circle fa-lg ms-2 text-danger"
                                                                            style="padding-top: 8px;cursor: pointer;"
                                                                            onclick="deleteQuestion(this)"
                                                                            title="Delete this question"
                                                                            data-url="{{ route('exam.deleteWrittenQuestion', $question->id) }}"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="card-body collaps-question bg-warning"
                                                                style="display: none;">
                                                                <input type="hidden" name="subject_id"
                                                                    value="{{ $q_subject->id }}">
                                                                {{-- <input type="hidden" class="serial_number"
                                                                    name="serial_number[]" value="{{ $present_serial }}"> --}}
                                                                <input type="hidden" name="written_id"
                                                                    value="{{ request()->written_id }}">
                                                                <input type="hidden" name="question_id[]"
                                                                    value="{{ $question->id }}">

                                                                <div class="col-md-12">
                                                                    <label for="">Question Name</label>
                                                                    <textarea class="form-control" rows="3" placeholder="Enter question name here"
                                                                        name="question_name_{{ $question->id }}">{!! $question->name !!}</textarea>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <label for="">Question Marks</label>
                                                                    <input class="form-control"
                                                                        placeholder="Enter question mark here"
                                                                        name="question_mark_{{ $question->id }}"
                                                                        value="{{ $question->mark }}">
                                                                </div>


                                                                <hr>
                                                            </div>
                                                        </div>
                                                    @endforeach

                                                    {{-- add multiple question from here --}}
                                                    <div id="subject_{{ $q_subject->id }}">
                                                    </div>

                                                    <button type="submit" class="btn btn-primary "
                                                        id="question-submit-button-{{ $q_subject->id }}"
                                                        @if (count($present_question) > 0) style="display: block;" @else style="display: none;" @endif>Update
                                                        or Create</button>

                                                </div>
                                            </form>

                                            <div class="card-footer" style="text-align: right;">

                                                <button class="btn btn-primary" type="button"
                                                    onclick="addAnotherQuestion(this, '{{ $q_subject->id }}')"
                                                    data-serial_number="{{ count($present_question) > 0 ? 1 + $present_serial : 1 }}">Create
                                                    Another Question</button>
                                                <div class="add-more-question" style="display: none;">

                                                    {{-- below html div will add --}}
                                                    <div class="mb-2">
                                                        <div class="alert alert-success" style="margin-bottom: 1px;">
                                                            <div class="d-flex justify-content-between">
                                                                <h4>Question Number # <span
                                                                        class="serial_number_{{ $q_subject->id }}"></span>
                                                                </h4>
                                                                <div class="d-flex justify-content-end">
                                                                    <i class="fas fa-minus-circle fa-lg"
                                                                        style="padding-top: 8px;display: none;cursor: pointer;"
                                                                        onclick="closeQuestion(this)" title="Collapse"></i>
                                                                    <i class="fas fa-plus-circle fa-lg"
                                                                        style="padding-top: 8px;cursor: pointer;"
                                                                        onclick="openQuestion(this)" title="Expand"></i>
                                                                    <i class="fas fa-times-circle fa-lg ms-2 text-danger"
                                                                        style="padding-top: 8px;cursor: pointer;"
                                                                        onclick="removeQuestion(this)" title="Remove"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card-body collaps-question bg-warning"
                                                            style="display: none;">
                                                            <input type="hidden" name="subject_id"
                                                                value="{{ $q_subject->id }}">
                                                            <input type="hidden" class="serial_number">
                                                            <input type="hidden" name="written_id"
                                                                value="{{ request()->written_id }}">

                                                            <div class="col-md-12">
                                                                <label for="">Question Name</label>
                                                                <textarea class="question_name form-control" rows="3" placeholder="Enter question name here" name=""></textarea>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <label for="">Question Marks</label>
                                                                <input class="question_mark form-control" type="number"
                                                                    placeholder="Enter question mark here" name="">
                                                            </div>


                                                            <hr>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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

@section('js')
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/KaTeX/0.9.0/katex.min.js"></script> --}}
    <script src="{{ asset('summernote-math.js') }}"></script>

    <script>
        function addAnotherQuestion(e, subject_id) {
            var serial_number = parseInt($(e).data("serial_number"));

            $(e).parent().find(".serial_number_" + subject_id).html(serial_number);

            $(e).parent().find(".question_name").attr("name", "question_name[]");
            $(e).parent().find(".question_mark").attr("name", "question_mark[]");
            $(e).parent().find(".serial_number").attr("name", "serial_number[]");
            $(e).parent().find(".serial_number").val(serial_number);

            var data = $(e).parent().find('.add-more-question').html();
            $("#subject_" + subject_id).before(data);
            $("#question-submit-button-" + subject_id).show();
            // $(e).parent().find(".question_name").addClass('summernote');

            $(e).data('serial_number', ++serial_number);
        }

        function openQuestion(e) {
            $(e).parent().parent().parent().parent().find('.collaps-question').show();

            $(e).parent().find('.fa-minus-circle').show();
            $(e).parent().find('.fa-plus-circle').hide();
        }

        function closeQuestion(e) {
            $(e).parent().parent().parent().parent().find('.collaps-question').hide();

            $(e).parent().find('.fa-minus-circle').hide();
            $(e).parent().find('.fa-plus-circle').show();
        }

        function removeQuestion(e) {
            $(e).parent().parent().parent().parent().remove();
        }

        function deleteQuestion(e) {
            var url = $(e).data('url');

            $.ajax({
                method: 'get',
                url: url,
                cache: false,
                success: function(response) {

                    if (response.status == true) {
                        Toast.fire({
                            icon: 'success',
                            title: 'Question deleted successfully'
                        });
                        $(e).parent().parent().parent().parent().remove();
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: 'Something went wrong! Please try again.'
                        })
                    }

                },
                async: false,
                error: function(error) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Something went wrong! Please try again.'
                    })
                }
            });
        }
    </script>

@endsection
