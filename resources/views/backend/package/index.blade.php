@extends('backend.layouts.master')
@section('title', 'All package list')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">List of Package</h3>
                    <ol class="breadcrumb page_bradcam mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $company->name }} </a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Package</a></li>
                        <li class="breadcrumb-item active">Index</li>
                    </ol>
                </div>
                <a href="{{ route('packages.createOrEdit') }}" class="white_btn3">Create Package</a>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        @foreach ($package as $item)
            <div class="col-md-6">
                <div class="card mb-3 widget-chart">
                    <div class="icon-wrapper rounded-circle" style="overflow: visible">
                        <div class="icon-wrapper-bg" style="opacity: 1;">
                            <img src="{{ asset($item->image) }}" style="height: 54px;">
                        </div>
                    </div>
                    <div class="widget-numbers">Price: <span>{{ $item->amount }}</span> BDT</div>
                    <div class="widget-subheading">{{ $item->name }}</div>
                    <br>
                    <div class="widget-subheading">Validity: {{ $item->validity }} Days, Status:
                        {{ $item->status == 1 ? 'Active' : 'Inactive' }} <br> Package type: {{ $item->package_type }}
                        <br>Total Purchased: {{ $item->package_history_count }} Students, Total sell amount:
                        {{ $item->packageHistory->sum('amount') }} BDT
                        @if($item->url)
                            <br><strong>URL:</strong> <a href="{{ $item->url }}" target="_blank" class="text-primary">{{ $item->url }}</a>
                        @endif
                    </div>
                    <hr>
                    @if ($item->details)
                        <div class="widget-subheading">{!! $item->details !!}</div>
                    @endif


                    <div class="widget-description text-success" style="text-align: left;">
                        @foreach ($item->permission as $c_key => $cat)
                            @if (is_array($cat))
                                @foreach ($cat as $s_key => $sub)
                                    @if (is_array($sub))
                                        @foreach ($sub as $ch_key => $child)
                                            @if (is_array($child))
                                                @if($s_key == 'BCS' || $s_key == 'Bank' || $s_key == 'Recent' || $s_key == 'Record_Class')
                                                    <i class="fas fa-check-circle me-2"></i>{{ $c_key }}
                                                    <i class="fas fa-arrow-right"></i> {{ ' ' . $s_key }}
                                                    <i class="fas fa-arrow-right"></i>
                                                    {{ \App\Models\MaterialFolder::find($ch_key)->name ?? '' }}
                                                    <i class="fas fa-arrow-right"></i>
                                                    @foreach ($child as $e_key => $exam)
                                                        {{ \App\Models\MaterialFolder::find($e_key)->name ?? '' }} : Permitted
                                                    @endforeach

                                                @elseif($c_key == 'Revision' && $s_key == 'SUBJECT')
                                                    @foreach ($child as $e_key => $exam)
                                                        <div>
                                                            <i class="fas fa-check-circle me-2"></i>{{ $c_key }}
                                                            <i class="fas fa-arrow-right"></i> {{ \App\Models\Subject::find($ch_key)->name ?? $ch_key }}
                                                            @if($e_key == 'TOPIC' && is_array($exam))
                                                                @foreach($exam as $topic_id => $topic_val)
                                                                    <i class="fas fa-arrow-right"></i> {{ \App\Models\TopicSource::find($topic_id)->topic ?? $topic_id }}
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                @else
                                                    @foreach ($child as $e_key => $exam)
                                                        <div>
                                                            <i class="fas fa-check-circle me-2"></i>{{ $c_key }}
                                                            <i class="fas fa-arrow-right"></i> {{ $s_key }}
                                                            <i class="fas fa-arrow-right"></i> {{ $ch_key }}
                                                            <i class="fas fa-arrow-right"></i>
                                                            @php
                                                                $examRecord = $ch_key == 'Preliminary' ? \App\Models\Exam::find($e_key) : \App\Models\Written::find($e_key);
                                                            @endphp
                                                            {{ $examRecord ? $examRecord->published_at->format('d-m-Y') : 'N/A' }}{{ ': ' . $exam == true ? 'Permitted' : '' }}
                                                        </div>
                                                    @endforeach
                                                @endif
                                            @else
                                                <div>
                                                    <i class="fas fa-check-circle me-2"></i>{{ $c_key }}
                                                    <i class="fas fa-arrow-right"></i> {{ ' ' . $s_key }}
                                                    <i class="fas fa-arrow-right"></i>

                                                    @if ($s_key == 'Preliminary')
                                                        @php
                                                            $examRecord = \App\Models\Exam::find($ch_key);
                                                        @endphp
                                                        {{ $examRecord ? $examRecord->published_at->format('d-m-Y') : 'N/A' }}
                                                    @elseif($s_key == 'Written')
                                                        @php
                                                            $writtenRecord = \App\Models\Written::find($ch_key);
                                                        @endphp
                                                        {{ $writtenRecord ? $writtenRecord->published_at->format('d-m-Y') : 'N/A' }}
                                                    @elseif($c_key == 'Revision' && $s_key == 'SUBJECT')
                                                        {{ \App\Models\Subject::find($ch_key)->name ?? $ch_key }}
                                                    @else
                                                        @if($s_key == 'BCS' || $s_key == 'Bank' || $s_key == 'Recent' || $s_key == 'Record_Class')
                                                            {{ \App\Models\MaterialFolder::find($ch_key)->name ?? '' }}
                                                        @else
                                                            {{ $ch_key }}
                                                        @endif
                                                    @endif
                                                    {{ ': ' . $child == true ? ': Permitted' : '' }}
                                                </div>
                                            @endif
                                        @endforeach
                                    @else
                                        <div>
                                            <i class="fas fa-check-circle me-1"></i>
                                            {{ $c_key }} <i class="fas fa-arrow-right"></i>
                                            {{ $s_key . ': ' . $sub == true ? 'Permitted' : '' }}

                                        </div>
                                    @endif
                                @endforeach
                            @else
                                <div>
                                    <i class="fas fa-check-circle me-1"></i>{{ $c_key }} <i
                                        class="fas fa-arrow-right"></i> {{ $cat == true ? 'Permitted' : '' }}

                                </div>
                            @endif
                        @endforeach
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('packages.createOrEdit', $item->id) }}" class="btn btn-info btn-sm">Edit</a>
                        <form action="{{ route('packages.delete', $item->id) }}" method="post">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="d-flex justify-content-center mt-4">
            {{ $package->links() }}
        </div>
    </div>
@endsection
