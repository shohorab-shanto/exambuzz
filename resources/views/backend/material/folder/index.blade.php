@extends('backend.layouts.master')
@section('title', 'All ' . request()->ref . ' material list')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">List of{{ request()->ref }} Material Folder</h3>

                    <ol class="breadcrumb page_bradcam mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $company->name }} </a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">{{ request()->child }}Folder</a></li>
                        <li class="breadcrumb-item active">Index</li>
                    </ol>

                </div>

                <a href="{{ route('material.folder.create') }}" class="white_btn3">Create Material
                    Folder</a>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="white_card card_height_100 mb_30">
                <div class="white_card_body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col" style="width: 120px;">Action</th>
                                    <th scope="col">Material Name</th>
                                    <th scope="col">Folder Name</th>
                                    <th scope="col">Parent Folder Name</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($material as $item)
                                    <tr>
                                        <th scope="row">{{ $loop->iteration }}</th>
                                        <td>
                                            <div class="d-flex justify-content-between">
                                                <a href="{{ route('material.folder.edit', $item->id) }}" class="btn btn-info me-2">
                                                    <i class="far fa-edit"></i>
                                                </a>

                                                <form action="{{ route('material.folder.destroy', $item->id) }}" method="post">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit" onclick="return confirm('Are you sure?')" class="btn btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        <td>{{ $item->type }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>
                                            @if($item->parent_id)
                                                {{ $item->parent->name }} <!-- Display parent folder name -->
                                            @else
                                                <span class="text-muted">(Root)</span> <!-- If it's a root folder -->
                                            @endif
                                        </td>
                                        <td>{{ $item->status == '1' ? 'Active' : 'Inactive' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{ $material->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
