@extends('backend.layouts.master')
@section('title', isset($routine) ? 'Update' : 'Upload' . ' ' . ucfirst($type) . ' Exam Routine')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">{{ isset($routine) ? 'Update' : 'Upload' }} {{ ucfirst($type) }} Exam Routine</h3>

                    <ol class="breadcrumb page_bradcam mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $company->name }} </a></li>
                        <li class="breadcrumb-item"><a href="{{ route('class-routine.index') }}">Class Routine</a></li>
                        <li class="breadcrumb-item active">{{ isset($routine) ? 'Edit' : 'Create' }}</li>
                    </ol>

                </div>
            </div>
        </div>
    </div>
    
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="white_card card_height_100 mb_30">
                <div class="white_card_body">
                    <div class="card-body">
                        <form action="{{ route('class-routine.store') }}" method="post" enctype="multipart/form-data">
                            @csrf

                            <input type="hidden" name="type" value="{{ $type }}">

                            <div class="form-group mb-3">
                                <label class="form-label">Exam Type</label>
                                <input type="text" class="form-control" value="{{ ucfirst($type) }} Exam" readonly disabled>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Title (Optional)</label>
                                <input type="text" class="form-control" name="title"
                                       placeholder="e.g., BCS 47th {{ ucfirst($type) }} Exam Routine"
                                       value="{{ $routine->title ?? old('title') }}">
                                <small class="text-muted">Example: "BCS 47th Preliminary Exam Routine" or "Bank Job Written Exam 2025"</small>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">PDF File<span class="text-danger">*</span></label>
                                <input type="file" class="form-control" name="pdf_file" accept=".pdf" {{ isset($routine) ? '' : 'required' }}>
                                <small class="text-muted">Upload PDF file (Max: 2MB)</small>
                                
                                @if(isset($routine) && $routine->pdf_file)
                                    <div class="mt-2">
                                        <p class="mb-1"><strong>Current File:</strong></p>
                                        <a href="{{ asset('storage/' . $routine->pdf_file) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-file-pdf"></i> View Current PDF
                                        </a>
                                        <small class="text-muted d-block mt-1">Leave empty to keep current file, or upload new file to replace it.</small>
                                    </div>
                                @endif
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Status<span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="1" {{ isset($routine) && $routine->status == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ isset($routine) && $routine->status == 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                                <small class="text-muted">Only active routines will be visible to students in the mobile app.</small>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ isset($routine) ? 'Update' : 'Upload' }} Routine
                                </button>
                                <a href="{{ route('class-routine.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

