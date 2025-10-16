@extends('backend.layouts.master')
@section('title',
    request()->exam_id
    ? 'Update'
    : 'Create' .
    ' new ' .
    request()->ref .
    ' ' .
    request()->type .
    ' exam
    syllabus')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">{{ request()->child ?? request()->ref }}{{ ' ' . request()->type }}
                        exam syllabus</h3>
                    <ol class="breadcrumb page_bradcam mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $company->name }} </a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">{{ request()->ref . ' ' . request()->type }}
                                exam syllabus</a></li>
                        @if (request()->child)
                            <li class="breadcrumb-item"><a
                                    href="javascript:void(0);">{{ request()->child . ' ' . request()->type }}
                                    exam </a></li>
                        @endif
                        <li class="breadcrumb-item active">Upload</li>
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
                        <form action="{{ route('exam.uploadSyllabus') }}" method="post" enctype="multipart/form-data">
                            @csrf

                            <input type="hidden" name="category" value="{{ request()->ref }}">
                            <input type="hidden" name="subcategory" value="{{ request()->type }}">
                            <input type="hidden" name="childcategory" value="{{ request()->child }}">
                            <div class="row mb-3">
                                <div class="form-group">
                                    <label class="form-label">Syllabus(PDF)</label>
                                    <input type="file" name="syllabus" class="form-control">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </form>
                        <hr>
                        @if ($data && $data->syllabus != null)
                            <iframe src="{{ asset($data->syllabus) }}" frameborder="0"
                                style="width: 100%;height:600px;"></iframe>
                        @else
                            <h6>No syllabus uploaded yet</h6>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
@endsection
