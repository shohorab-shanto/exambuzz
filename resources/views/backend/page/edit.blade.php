@extends('backend.layouts.master')
@section('title', 'Update existing page')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">Update existing page</h3>
                    <ol class="breadcrumb page_bradcam mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $company->name }} </a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Page</a></li>
                        <li class="breadcrumb-item active">Edit</li>
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
                        <form action="{{ route('page.update', $page) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            @method('put')
                            <div class="mb-3">
                                <label class="form-label" for="inputAddress">Page title <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="inputAddress"
                                    placeholder="Enter page title here" name="name" value="{{ $page->name }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="inputAddress">Page details <span
                                        class="text-danger">*</span></label>
                                <textarea type="text" class="form-control" id="summernote" name="details">{!! $page->details !!}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
