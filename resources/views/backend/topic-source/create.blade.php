@extends('backend.layouts.master')
@section('title', 'Create new topic and source')
@section('content')
    <style>
        .hide {
            display: none;
        }
    </style>
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">Create new topic and source</h3>
                    <ol class="breadcrumb page_bradcam mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $company->name }} </a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Topic & Source</a></li>
                        <li class="breadcrumb-item active">Create</li>
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
                        <form action="{{ route('topic.source.store') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="inputAddress">Subject <span
                                        class="text-danger">*</span></label>
                                <select name="subject_id" class="form-control" id="" required>
                                    <option value="">Select</option>
                                    @foreach ($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="hdtuto control-group lst increment">
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group mb-3">
                                            <label class="form-label" for="inputAddress">Topic <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control w-100" id="inputAddress"
                                                placeholder="Enter topic here" name="topic[]" required>
                                        </div>

                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group mb-3">
                                            <label class="form-label" for="inputAddress">Source</label>
                                            <input type="text" class="form-control" id="inputAddress"
                                                placeholder="Enter source here" name="source[]">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="input-group-btn">
                                            <button class="btn btn-success" style="margin-top: 1.7rem;" type="button"><i
                                                    class="far fa-plus-square"></i>
                                                Add</button>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <button type="submit" class="btn btn-primary">Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="clone hide">
        <div class="hdtuto control-group lst" style="margin-top:10px">
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group mb-3">
                        <label class="form-label" for="inputAddress">Topic <span class="text-danger">*</span></label>
                        <input type="text" class="form-control w-100" id="inputAddress" placeholder="Enter topic here"
                            name="topic[]" required>
                    </div>

                </div>
                <div class="col-md-5">
                    <div class="form-group mb-3">
                        <label class="form-label" for="inputAddress">Source</label>
                        <input type="text" class="form-control" id="inputAddress" placeholder="Enter source here"
                            name="source[]">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="input-group-btn">
                        <button class="btn btn-danger" type="button" style="margin-top: 1.7rem"> <i
                                class="far fa-minus-square"></i>
                            Remove </button>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('js')
    {{-- for multiple file insertion --}}
    <script type="text/javascript">
        $(document).ready(function() {
            $(".btn-success").click(function() {
                var lsthmtl = $(".clone").html();
                $(".increment").after(lsthmtl);
            });
            $("body").on("click", ".btn-danger", function() {
                $(this).parents(".hdtuto").remove();
            });
            $('#images').on('change', function() {
                multiImgPreview(this, 'div.imgPreview');
            });
        });
    </script>
@endsection
