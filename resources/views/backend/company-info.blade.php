@extends('backend.layouts.master')
@section('title', 'Entire company information')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">Company Information</h3>
                    <ol class="breadcrumb page_bradcam mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $company->name }} </a></li>
                        <li class="breadcrumb-item active">Company Information</li>
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
                        <form action="{{ route('storeCompanyInfo') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row mb-3">
                                <div class=" col-md-6">
                                    <label class="form-label" for="inputPassword4">Company/Project Name <span
                                            class="text-danger">*</span> </label>
                                    <input type="text" class="form-control" id="inputPassword4"
                                           placeholder="Enter company or project name" name="name"
                                           value="{{ $info->name ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="inputEmail4">Email <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" class="form-control" id="inputEmail4" placeholder="Email"
                                           name="email" value="{{ $info->email ?? '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="inputAddress">Address</label>
                                <input type="text" class="form-control" id="inputAddress" placeholder="1234 Main St"
                                       name="address" value="{{ $info->address ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="inputAddress">About Company/Project <span
                                        class="text-danger">*</span> </label>
                                <input type="text" class="form-control" id="inputAddress"
                                       placeholder="A company/project summary" name="about" value="{{ $info->about ?? '' }}">
                            </div>
                            <div class="row mb-3">
                                <div class=" col-md-4">
                                    <label class="form-label" for="inputCity">Phone One <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="inputCity" name="phone_one"
                                           value="{{ $info->phone_one ?? '' }}">
                                </div>
                                <div class=" col-md-4">
                                    <label class="form-label" for="inputCity">Phone Two</label>
                                    <input type="text" class="form-control" id="inputCity" name="phone_two"
                                           value="{{ $info->phone_two ?? '' }}">
                                </div>
                                <div class=" col-md-4">
                                    <label class="form-label" for="inputZip">Phone Three</label>
                                    <input type="text" class="form-control" id="inputZip" name="phone_three"
                                           value="{{ $info->phone_three ?? '' }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class=" col-md-4">
                                    <label class="form-label" for="inputCity">Logo <span class="text-danger">*</span>
                                    </label>
                                    <input type="file" class="form-control" id="inputCity" name="logo">

                                    @if ($info->logo)
                                        <img src="{{ $info->logo }}" style="height: 40px;
                                        width: 180px;">
                                    @endif
                                </div>
                                <div class=" col-md-4">
                                    <label class="form-label" for="inputCity">Favicon <span class="text-danger">*</span>
                                    </label>
                                    <input type="file" class="form-control" id="inputCity" name="favicon">
                                    @if ($info->favicon)
                                        <img src="{{ $info->favicon }}" style="height:100px;">
                                    @endif
                                </div>
                                <div class=" col-md-4">
                                    <label class="form-label" for="inputZip">App logo</label>
                                    <input type="file" class="form-control" id="inputZip" name="app_logo">
                                    @if ($info->app_logo)
                                        <img src="{{ $info->app_logo }}" style="height:100px;">
                                    @endif
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="inputAddress">App Link</label>
                                <input type="url" class="form-control" id="inputAddress"
                                       placeholder="A company/project summary" name="app_link"
                                       value="{{ $info->app_link ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="inputAddress">Facebook</label>
                                <input type="url" class="form-control" id="inputAddress"
                                       placeholder="Enter facebook link" name="facebook"
                                       value="{{ $info->facebook ?? '' }}">
                            </div>
                            {{-- <div class="mb-3">
                                <label class="form-label" for="inputAddress">Twitter</label>
                                <input type="url" class="form-control" id="inputAddress"
                                    placeholder="Enter twitter link" name="twitter" value="{{ $info->twitter ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="inputAddress">Instagram</label>
                                <input type="url" class="form-control" id="inputAddress"
                                    placeholder="Enter instagram link" name="instagram"
                                    value="{{ $info->instagram ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="inputAddress">Youtube</label>
                                <input type="url" class="form-control" id="inputAddress"
                                    placeholder="Enter youtube link" name="youtube" value="{{ $info->youtube ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="inputAddress">Linkedin</label>
                                <input type="url" class="form-control" id="inputAddress"
                                    placeholder="Enter linkedin link" name="linkedin"
                                    value="{{ $info->linkedin ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="inputAddress">Pinterest</label>
                                <input type="url" class="form-control" id="inputAddress"
                                    placeholder="Enter pinterest link" name="pinterest"
                                    value="{{ $info->pinterest ?? '' }}">
                            </div> --}}


                            <div class="mb-3">
                                <label class="form-label" for="inputAddress">Telegram</label>
                                <input type="text" class="form-control" id="inputAddress"
                                       placeholder="Enter telegram link" name="telegram" value="{{ $info->telegram ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="inputAddress">Messenger</label>
                                <input type="text" class="form-control" id="inputAddress"
                                       placeholder="Enter messenger link" name="messenger"
                                       value="{{ $info->messenger ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="inputAddress">WhatsApp</label>
                                <input type="text" class="form-control" id="inputAddress"
                                       placeholder="Enter whatsapp link" name="whatsapp"
                                       value="{{ $info->whatsapp ?? '' }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label " for="inputAddress">Facebook Group</label>
                                <input type="url" class="form-control" id="inputAddress"
                                       placeholder="Enter facebook group link" name="facebook_group"
                                       value="{{ $info->facebook_group ?? '' }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="inputAddress">Telegram Group</label>
                                <input type="url" class="form-control" id="inputAddress"
                                       placeholder="Enter telegram group link" name="telegram_group"
                                       value="{{ $info->telegram_group ?? '' }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label " for="inputAddress">bKash Payment Enable</label>
                                <select class="form-select" name="bkash_payment_enable" id="bkash_payment_enable">
                                    <option value="1" {{ $info->bkash_payment_enable ? 'selected' : '' }}>Enable</option>
                                    <option value="0" {{ !$info->bkash_payment_enable ? 'selected' : '' }}>Disable</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">Update Company Information</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
