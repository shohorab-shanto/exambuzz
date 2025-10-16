@extends('backend.layouts.master')
@section('title', request()->material_id ? 'Update' : 'Create' . ' new ' . request()->ref . ' ' . ' exam')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">Create Material Folder</h3>

                    <ol class="breadcrumb page_bradcam mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $company->name }} </a>
                        </li>
                        <li class="breadcrumb-item"><a href="{{ route('material.folder.index') }}">{{ request()->child }}Folder</a>
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
                            action="{{ !isset($material) ? route('material.folder.store') : route('material.folder.update', $material->id) }}"
                            method="post" enctype="multipart/form-data">
                            @csrf

                            @if(isset($material))
                                @method("PUT")
                            @endif


                            <div class="form-group mb-3">
                                <label class="form-label">Material Name<span class="text-danger">*</span></label>

                                <select name="type" id="type" class="form-control" required>
                                    <option value="BCS" {{ isset($material) && $material->type == 'BCS' ? 'selected' : '' }}>BCS</option>
                                    <option value="Bank" {{ isset($material) && $material->type == 'Bank' ? 'selected' : '' }}>Bank</option>
                                    <option value="Routine" {{ isset($material) && $material->type == 'Routine' ? 'selected' : '' }}>Routine</option>
                                    <option value="Record Class" {{ isset($material) && $material->type == 'Record Class' ? 'selected' : '' }}>Record Class</option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label ">Parent Folder</label>
                                <select name="parent_id" id="parent_id" class="form-control">
                                    <option value="">--Root--</option>
                                    @foreach($folders as $folder)
                                        <option value="{{ $folder->id }}" {{ isset($material) && $material->parent_id == $folder->id ? 'selected' : '' }}>
                                            {{ $folder->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Material Name<span class="text-danger">*</span></label>

                                <input type="text" class="form-control" name="name"
                                       placeholder="Enter material folder name"
                                       value="{{ $material->name ?? '' }}" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Status<span class="text-danger">*</span></label>

                                <select name="status" id="status" class="form-control" required>
                                    <option value="1" {{ isset($material) && $material->status == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ isset($material) && $material->status == '0' ? 'selected' : '' }}>InActive</option>
                                </select>
                            </div>


                            <button type="submit" class="submit btn btn-primary">Save</button>

                            <a href="{{ route('material.folder.index') }}" class="btn btn-info float-end">Back</a>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
