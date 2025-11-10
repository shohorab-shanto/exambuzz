@extends('backend.layouts.master')
@section('title', 'Class Routine Management')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">Class Routine Management</h3>

                    <ol class="breadcrumb page_bradcam mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $company->name }} </a></li>
                        <li class="breadcrumb-item active">Class Routine</li>
                    </ol>

                </div>
            </div>
        </div>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Preliminary Exam Routine Card -->
        <div class="col-lg-6">
            <div class="white_card mb_30">
                <div class="white_card_header">
                    <div class="box_header m-0">
                        <div class="main-title">
                            <h3 class="m-0">Preliminary Exam Routine</h3>
                        </div>
                    </div>
                </div>
                <div class="white_card_body">
                    @php
                        $preliminary = $routines->where('type', 'preliminary')->first();
                    @endphp
                    
                    @if($preliminary)
                        <div class="mb-3">
                            <p class="mb-1"><strong>Title:</strong> {{ $preliminary->title ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Status:</strong> 
                                <span class="badge {{ $preliminary->status ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $preliminary->status ? 'Active' : 'Inactive' }}
                                </span>
                            </p>
                            @if($preliminary->pdf_file)
                                <p class="mb-1"><strong>File:</strong> 
                                    <a href="{{ asset('storage/' . $preliminary->pdf_file) }}" target="_blank" class="text-primary">
                                        <i class="fas fa-file-pdf"></i> View PDF
                                    </a>
                                </p>
                            @endif
                            <p class="mb-1"><strong>Updated:</strong> {{ $preliminary->updated_at->format('d M, Y h:i A') }}</p>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <a href="{{ route('class-routine.create-edit', 'preliminary') }}" class="btn btn-primary btn-sm">
                                <i class="far fa-edit"></i> Edit
                            </a>
                            
                            <form action="{{ route('class-routine.toggle-status', 'preliminary') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-{{ $preliminary->status ? 'warning' : 'success' }} btn-sm">
                                    <i class="fas fa-toggle-{{ $preliminary->status ? 'off' : 'on' }}"></i>
                                    {{ $preliminary->status ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            
                            <form action="{{ route('class-routine.destroy', 'preliminary') }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this routine?')" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    @else
                        <p class="text-muted mb-3">No preliminary exam routine uploaded yet.</p>
                        <a href="{{ route('class-routine.create-edit', 'preliminary') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Upload Routine
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Written Exam Routine Card -->
        <div class="col-lg-6">
            <div class="white_card mb_30">
                <div class="white_card_header">
                    <div class="box_header m-0">
                        <div class="main-title">
                            <h3 class="m-0">Written Exam Routine</h3>
                        </div>
                    </div>
                </div>
                <div class="white_card_body">
                    @php
                        $written = $routines->where('type', 'written')->first();
                    @endphp
                    
                    @if($written)
                        <div class="mb-3">
                            <p class="mb-1"><strong>Title:</strong> {{ $written->title ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Status:</strong> 
                                <span class="badge {{ $written->status ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $written->status ? 'Active' : 'Inactive' }}
                                </span>
                            </p>
                            @if($written->pdf_file)
                                <p class="mb-1"><strong>File:</strong> 
                                    <a href="{{ asset('storage/' . $written->pdf_file) }}" target="_blank" class="text-primary">
                                        <i class="fas fa-file-pdf"></i> View PDF
                                    </a>
                                </p>
                            @endif
                            <p class="mb-1"><strong>Updated:</strong> {{ $written->updated_at->format('d M, Y h:i A') }}</p>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <a href="{{ route('class-routine.create-edit', 'written') }}" class="btn btn-primary btn-sm">
                                <i class="far fa-edit"></i> Edit
                            </a>
                            
                            <form action="{{ route('class-routine.toggle-status', 'written') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-{{ $written->status ? 'warning' : 'success' }} btn-sm">
                                    <i class="fas fa-toggle-{{ $written->status ? 'off' : 'on' }}"></i>
                                    {{ $written->status ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            
                            <form action="{{ route('class-routine.destroy', 'written') }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this routine?')" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    @else
                        <p class="text-muted mb-3">No written exam routine uploaded yet.</p>
                        <a href="{{ route('class-routine.create-edit', 'written') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Upload Routine
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

