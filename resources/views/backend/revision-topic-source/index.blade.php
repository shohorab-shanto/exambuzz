@extends('backend.layouts.master')
@section('title', 'All topics and sources')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">List of topics and sources</h3>
                    <ol class="breadcrumb page_bradcam mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $company->name }} </a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Subject</a></li>
                        <li class="breadcrumb-item active">Index</li>
                    </ol>
                </div>
                <a href="{{ route('revision_topic.source.create') }}" class="white_btn3">Create Topic & Source</a>
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
                                    <th scope="col">Action</th>
                                    <th scope="col">Subject Title</th>
                                    <th scope="col">Topic</th>
                                    <th scope="col">Source</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topic_source as $item)
                                    <tr>
                                        <th scope="row">{{ $loop->iteration }}</th>
                                        <td>

                                            <a href="{{ route('revision_topic.source.mcqQuestion', $item->id) }}"
                                               class="btn btn-info me-2"
                                               data-bs-toggle="tooltip"
                                               title="Create Question"
                                            >
                                                <i class="fas fa-file-signature"></i>
                                            </a>

                                            <a href="{{ route('revision_topic.source.edit', $item) }}"
                                               data-bs-toggle="tooltip"
                                               title="Edit Topic & Source"
                                               class="btn btn-info me-2">
                                                <i class="far fa-edit"></i>
                                            </a>

                                            <form action="{{ route('revision_topic.source.delete',$item) }}" method="post" style="display: inline-block" onsubmit="return confirm('Are you sure you want to delete this item?')">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>

                                        </td>
                                        <td>{{ $item->subject?->name }}</td>
                                        <td>{{ $item->topic }}</td>
                                        <td>{{ $item->source }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
