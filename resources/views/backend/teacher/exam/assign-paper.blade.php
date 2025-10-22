@extends('backend.layouts.master')
@section('title', 'Assign paper to teacher')
@section('css')
<style>
    .modal-backdrop {
        z-index: 1040 !important;
    }
    .modal {
        z-index: 1050 !important;
        background: none !important;
    }
    .modal-dialog {
        z-index: 1060 !important;
    }
    .modal-content {
        z-index: 1070 !important;
        position: relative;
    }
</style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">Assign paper to teacher</h3>
                    <ol class="breadcrumb page_bradcam mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $company->name }} </a></li>
                        <li class="breadcrumb-item"><a href="javascript:;">{{ request()->category }}</a></li>
                        <li class="breadcrumb-item active">Assign paper</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">

        <div class="col-lg-12">
            <div class="white_card card_height_100 mb_30">
                <div class="white_card_body">
                    <div class="alert alert-danger">
                        <b>
                            <a href="{{ asset($written->question) }}" class="btn btn-warning">View Question</a>
                        </b>
                        <br>
                        @php
                            $subjects = DB::table('subjects')
                                ->whereIn('id', explode(',', $written->subject_id))
                                ->get();

                            $topic = DB::table('topic_sources')
                                ->whereIn('id', explode(',', $written->topic_id))
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
                    <div class="card-body">
                        <form action="" method="get"
                              enctype="multipart/form-data">

                            <div class="row">
                                <div class="col-2">
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="text" name="student_id" class="form-control" aria-label="Small"
                                               aria-describedby="inputGroup-sizing-sm">
                                    </div>
                                </div>

                                <div class="col-4">
                                    <button class="btn btn-primary btn-sm">Search</button>
                                </div>
                            </div>

                        </form>

                        <form action="{{ route('teacher.written.storeAssignPaper') }}" method="post"
                              enctype="multipart/form-data">
                            @csrf
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Sl</th>
                                        <th><input type="checkbox" class="check_all"> All Check</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($paper) > 0)
                                        @php
                                            $isAnyStudentScriptNull = false;
                                        @endphp
                                        @foreach ($paper as $key => $item)
                                            @foreach ($item->writtenAnswerQuestion as $question)
                                                @foreach ($question->writtenAnswerQuestionScript as $script)
                                                    @if (is_null($script->student_script))
                                                        @php
                                                            $isAnyStudentScriptNull = true;
                                                        @endphp
                                                    @endif
                                                @endforeach
                                            @endforeach

                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <div class="form-check">
                                                        <input type="checkbox"
                                                               class="form-check-input {{ $item->teacher_id != null ? '' : 'custom_name' }}"
                                                               name="written_answer_id[]" value="{{ $item->id }}"
                                                               id="{{ $item->id }}"
                                                            {{ $item->teacher_id != null ? 'checked disabled' : '' }}>
                                                        <label class="form-check-label" for="{{ $item->id }}">
                                                            {{ $item->user->registration_id }} -{{ $item->teacher->name ?? 'Not assigned' }}

                                                            @if($isAnyStudentScriptNull)
                                                                <span class="text-danger">No script uploaded</span>
                                                            @endif
                                                        </label>
                                                    </div>
                                                </td>
                                                @if ($item->teacher && $item->is_checked == 0)
                                                    <td>
                                                        <a href="{{ route('teacher.written.removedAssignTeacher', $item->id) }}"
                                                           class="btn btn-danger btn-sm">Remove Teacher</a>
                                                    </td>
                                                @elseif ($item->teacher && $item->is_checked == 1)
                                                    <td>
                                                        <button type="button" class="btn btn-success btn-sm">Paper
                                                            Checked
                                                        </button>

                                                        {{-- <a href="{{ route('teacher.written.recheckAssignTeacher', $item->id) }}"
                                                           onclick="return confirm('Are you sure want to recheck this paper?')"
                                                           class="btn btn-warning btn-sm">Recheck Able</a> --}}

                                                        <button type="button" class="btn btn-primary btn-sm" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#reassignModal{{ $item->id }}">
                                                            Reassign Teacher
                                                        </button>
                                                    </td>
                                                @elseif ($item->teacher && $item->is_checked == 2)
                                                    <td>
                                                        <button type="button" class="btn btn-info btn-sm">Assigned For
                                                            Recheck
                                                        </button>
                                                        <span class="badge bg-secondary">Teacher: {{ $item->teacher->name }}</span>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td></td>
                                            <td>No exam paper submitteb</td>
                                            <td></td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            <div class="mb-3">
                                <label class="form-label" for="inputAddress">Teacher <span
                                        class="text-danger">*</span></label>
                                <select name="teacher_id" class="form-control" id="">
                                    <option value="">Select</option>
                                    @foreach ($teacher as $s_item)
                                        <option value="{{ $s_item->id }}">{{ $s_item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </form>
                        <div class="text-right mt-5">
                            {{ $paper->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reassign Teacher Modals (Outside table for proper z-index) -->
    @if (count($paper) > 0)
        @foreach ($paper as $item)
            @if ($item->teacher && $item->is_checked == 1)
                <div class="modal fade" id="reassignModal{{ $item->id }}" tabindex="-1" 
                     aria-labelledby="reassignModalLabel{{ $item->id }}" aria-hidden="true" 
                     data-backdrop="static" data-keyboard="true" style="z-index: 1050;">
                    <div class="modal-dialog modal-dialog-centered" style="z-index: 1060;">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="reassignModalLabel{{ $item->id }}">
                                    Reassign Teacher for Recheck
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('teacher.written.reassignTeacherForRecheck') }}" method="POST">
                                @csrf
                                <input type="hidden" name="paper_id" value="{{ $item->id }}">
                                <div class="modal-body">
                                    <div class="alert alert-info">
                                        <strong>Student:</strong> {{ $item->user->registration_id }} - {{ $item->user->name }}<br>
                                        <strong>Current Teacher:</strong> {{ $item->teacher->name }}<br>
                                        <strong>Obtained Mark:</strong> {{ $item->obtained_mark }}
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Select New Teacher <span class="text-danger">*</span></label>
                                        <select name="new_teacher_id" class="form-control" required>
                                            <option value="">Select Teacher</option>
                                            @foreach ($teacher as $s_item)
                                                @if($s_item->id != $item->teacher_id)
                                                    <option value="{{ $s_item->id }}">{{ $s_item->name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Reassign for Recheck</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endif
@endsection
@section('js')
    <script>
        $(document).ready(function () {
            $('.check_all').click(function () {
                $('.custom_name').prop('checked', $(this).prop('checked'));
            });

            $('.custom_name').click(function () {
                var allChecked = $('.custom_name:checked').length === $('.custom_name').length;
                $('.check_all').prop('checked', allChecked);
            });

            // Fix modal z-index issues - ensure modal appears above backdrop
            $('[id^="reassignModal"]').on('show.bs.modal', function (e) {
                var modal = $(this);
                var backdrop = $('.modal-backdrop');
                
                // Ensure backdrop is behind modal
                backdrop.css('z-index', '1040');
                modal.css('z-index', '1050');
                modal.find('.modal-dialog').css('z-index', '1060');
                modal.find('.modal-content').css('z-index', '1070');
                
                // Move modal to body if it's not already there
                if (!modal.parent().is('body')) {
                    modal.appendTo('body');
                }
            });

            // Clean up after modal closes
            $('[id^="reassignModal"]').on('hidden.bs.modal', function (e) {
                $('.modal-backdrop').remove();
            });
        });
    </script>
@endsection
