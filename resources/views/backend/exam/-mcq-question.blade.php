<div class="mb-2">
    <div class="alert alert-success" style="margin-bottom: 1px;">
        <div class="d-flex justify-content-between">
            <h4>Question Number # <span
                    class="serial_number_{{ $q_subject->id }}"></span>
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
                    onclick="removeQuestion(this)" title="Remove"></i>
            </div>
        </div>
    </div>
    <div class="card-body collaps-question bg-warning"
        style="display: none;">
        <input type="hidden" name="subject_id"
            value="{{ $q_subject->id }}">
        <input type="hidden" class="serial_number">
        <input type="hidden" name="exam_id"
            value="{{ request()->exam_id }}">

        <div class="col-md-12">
            <label for="">Question Name</label>
            <textarea class="question_name " placeholder="Enter question name here" name="" rows="5"
                style="width: 100%;"></textarea>
        </div>

        <div class="col-md-12 mt-2">
            <div class="card card-outline card-info"
                style="border-radius: 5px;">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h3 class="card-title">
                                Options with Answer
                            </h3>
                        </div>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="p-5">
                    <div class="form-group">
                        @for ($i = 0; $i < 4; $i++)
                            <div class="d-flex justify-content-start">
                                <div class="icheck-success d-inline">
                                    <input type="radio"
                                        class="question_option"
                                        value="{{ $i }}"
                                        @if ($i == 0) {{ 'checked' }} @endif>
                                    <label
                                        for="is_answer_{{ $q_subject->id }}_{{ $i }}">
                                    </label>
                                </div>
                                <textarea class="question_option_name " rows="2" style="width: 100%;"></textarea>
                            </div>
                            <br>
                            <br>
                        @endfor

                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <label for="">Question Explanation</label> <br>
            <textarea rows="5" style="width: 100%;" class="question_explanation "
                placeholder="Enter question explanation here"></textarea>
        </div>
        <hr>
    </div>
</div>












































<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel 8 Add/Remove Multiple Input Fields Example</title>
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 600px;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">

</head>

<body>
    <div class="container">
        <form action="{{ route('exam.createOrUpdateMCQQuestion', 1) }}" method="POST">
            @csrf
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if (Session::has('success'))
                <div class="alert alert-success text-center">
                    <p>{{ Session::get('success') }}</p>
                </div>
            @endif
            <table class="table table-bordered" id="dynamicAddRemove">
                <tr>
                    <th>Subject</th>
                    <th>Action</th>
                </tr>
                <tr>
                    <td>
                        <textarea name="addMoreInputFields[]" placeholder="Enter subject" class="form-control summernote"></textarea>
                    </td>
                    <td><button type="button" name="add" id="dynamic-ar" class="btn btn-outline-primary">Add
                            Subject</button></td>
                </tr>
            </table>
            <button type="submit" class="btn btn-outline-success btn-block">Save</button>
        </form>
    </div>
</body>
<!-- JavaScript -->
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous">
</script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"
    integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous">
</script>
<!-- Summernote JS -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/KaTeX/0.9.0/katex.min.js"></script>
    <script src="{{ asset('summernote-math.js') }}"></script>
<script type="text/javascript">
    var i = 0;
    $("#dynamic-ar").click(function() {
        ++i;
        $("#dynamicAddRemove").append('<tr><td><textarea type="text" name="addMoreInputFields[]" placeholder="Enter subject" class="form-control summernote" ></textarea></td><td><button type="button" class="btn btn-outline-danger remove-input-field">Delete</button></td></tr>'
        );
        $('.summernote').summernote({
            height: 100,
            toolbar: [
                ['fontsize', ['10', '25']],
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['insert', ['picture', 'link', 'math']],
                ['para', ['paragraph']],
                ['misc', ['codeview']]
            ],
        });
    });
    $(document).on('click', '.remove-input-field', function() {
        $(this).parents('tr').remove();
    });
    $('.summernote').summernote({
        height: 100,
        toolbar: [
            ['fontsize', ['10', '25']],
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['insert', ['picture', 'link', 'math']],
            ['para', ['paragraph']],
            ['misc', ['codeview']]
        ],
    });
</script>

</html>
