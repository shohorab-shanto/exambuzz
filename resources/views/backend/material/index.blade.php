@extends('backend.layouts.master')
@section('title', 'All ' . request()->ref . ' material list')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">List of
                        {{ request()->ref }} Material</h3>
                    <ol class="breadcrumb page_bradcam mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $company->name }} </a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">{{ request()->ref }}</a></li>
                        @if (request()->child)
                            <li class="breadcrumb-item"><a href="javascript:void(0);">{{ request()->child }}
                                    material</a></li>
                        @endif
                        <li class="breadcrumb-item active">Index</li>
                    </ol>
                </div>
                <a href="{{ route('material.create', ['ref' => request()->ref]) }}" class="white_btn3">Create Material</a>
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
                                    <th scope="col">Material Name</th>
                                    <th scope="col">Folder Name</th>
                                    <th scope="col">Subjects Name</th>
                                    <th scope="col">Topic & Source Name</th>
                                    <th scope="col">What will learn</th>
                                    @if (request()->ref !== 'Record Class')
                                        <th scope="col">PDF</th>
                                    @else
                                        <th scope="col">Video</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($material as $item)
                                    <tr>
                                        <th scope="row">{{ $loop->iteration }}</th>
                                        <td>
                                            <div class="d-flex justify-content-between">
                                                <a href="{{ route('material.create', [$item->id, 'ref' => request()->ref]) }}"
                                                    class="btn btn-info me-2">
                                                    <i class="far fa-edit"></i>
                                                </a>

                                                <form action="{{ route('material.delete', $item) }}" method="post">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit" onclick="return confirm('Are you sure?')"
                                                        class="btn btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->folders->pluck('name')->implode(' || ') ?? '' }}</td>
                                        <td>
                                            @foreach ($item->subjects as $subject)
                                                {{ $subject->name }}
                                                @if (!$loop->last)
                                                    {{ ',' }}<br>
                                                @endif
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach ($item->topics as $topics)
                                                {{ $topics->topic }} -
                                                {{ $topics->source }}
                                                @if (!$loop->last)
                                                    {{ ',' }}<br>
                                                @endif
                                            @endforeach
                                        </td>
                                        <td>
                                            {!! $item->details !!}
                                        </td>
                                        @if (request()->ref !== 'Record Class')
                                            <td>
                                                <a href="{{ asset($item->pdf) }}" target="_blank">PDF</a>
                                            </td>
                                        @else
                                            <td>
                                                <iframe width="200" height="100" src="{{ $item->video }}"
                                                    title="YouTube video player" frameborder="0"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                    allowfullscreen></iframe>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
{{--                        <div class="d-flex justify-content-center">--}}
{{--                            {{ $material->links() }}--}}
{{--                        </div>--}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
