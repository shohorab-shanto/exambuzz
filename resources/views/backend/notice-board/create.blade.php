@extends('backend.layouts.master')
@section('title', request()->material_id ? 'Update' : 'Create' . ' new ' . request()->ref . ' ' . ' exam')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">Create Notice Board</h3>

                    <ol class="breadcrumb page_bradcam mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $company->name }} </a>
                        </li>
                        <li class="breadcrumb-item"><a href="{{ route('notice-board.index') }}">{{ request()->child }}Notice Board</a>
                        </li>
                        <li class="breadcrumb-item active">Index</li>
                    </ol>

                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">

        <div class="col-lg-5">
            <div class="white_card card_height_100 mb_30">
                <div class="white_card_body">
                    <div class="card-body">
                        <form
                            action="{{ !isset($material) ? route('notice-board.store') : route('notice-board.update', $material->id) }}"
                            method="post" enctype="multipart/form-data">
                            @csrf

                            @if(isset($material))
                                @method("PUT")
                            @endif


                            <div class="form-group mb-3">
                                <label class="form-label">Title<span class="text-danger">*</span></label>

                                <input type="text" class="form-control" name="title"
                                       placeholder="Enter title"
                                       value="{{ $material->title ?? '' }}" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Description<span class="text-danger">*</span></label>

                                <textarea name="description" id="description" cols="30" rows="5" class="form-control summernote">{{ $material->description ?? '' }}</textarea>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Status<span class="text-danger">*</span></label>

                                <select name="status" id="status" class="form-control" required>
                                    <option value="1" {{ isset($material) && $material->status == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ isset($material) && $material->status == '0' ? 'selected' : '' }}>InActive</option>
                                </select>
                            </div>


                            <button type="submit" class="submit btn btn-primary">Save</button>

                            <a href="{{ route('notice-board.index') }}" class="btn btn-info float-end">Back</a>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
