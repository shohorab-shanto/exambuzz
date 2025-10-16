@extends('backend.layouts.master')
@section('title', 'All ' . request()->ref . ' withdrawal request list')
@section('content')

    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <div class="row">
        <div class="col-12">
            <div class="page_title_box d-flex align-items-center justify-content-between">
                <div class="page_title_left">
                    <h3 class="f_s_30 f_w_700 text_white">List of
                        {{ request()->ref }} withdrawal request</h3>
                    <ol class="breadcrumb page_bradcam mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ $company->name }} </a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Wallet Withdrawal Request</a></li>
                        <li class="breadcrumb-item active">{{ request()->ref }}</li>
                    </ol>
                </div>
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
                                    @if (request()->ref === 'Paid' || request()->ref === 'Declined')
                                    @else
                                        <th scope="col">Action</th>
                                    @endif
                                    <th scope="col">Teacher Name</th>
                                    @if (request()->ref === 'Paid')
                                        <th>Payment Method</th>
                                        <th>TrxId or A/C</th>
                                    @endif
                                    <th scope="col">Amount</th>
                                    <th scope="col">Requested</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $item)
                                    <tr>
                                        <th scope="row">{{ $loop->iteration }}</th>
                                        @if (request()->ref === 'Paid' || request()->ref === 'Declined')
                                        @else
                                            <td>
                                                <div class="d-flex justify-content-around">
                                                    @if (request()->ref === 'Pending')
                                                        <!-- Button trigger modal -->
                                                        <div class="w3-container">
                                                            <button type="button"
                                                                class="btn btn-outline-primary btn-sm mr-2"
                                                                onclick="document.getElementById('{{ $loop->iteration }}').style.display='block'">
                                                                Paid
                                                            </button>

                                                            <div id="{{ $loop->iteration }}" class="w3-modal">
                                                                <div class="w3-modal-content">
                                                                    <div class="w3-container">
                                                                        <span
                                                                            onclick="document.getElementById('{{ $loop->iteration }}').style.display='none'"
                                                                            class="w3-button w3-display-topright">&times;</span>
                                                                        <div class="p-5">
                                                                            <div class="text-centar p-3">
                                                                                <h4>Kepp some manual transaction record</h4>
                                                                            </div>
                                                                            <form
                                                                                action="{{ route('teacher.updateWithdrawalRequest', $item->id) }}"
                                                                                method="post">
                                                                                @csrf
                                                                                <input type="hidden" name="type"
                                                                                    value="Paid">
                                                                                <div class="form-group mb-2">
                                                                                    <label for="">Payment
                                                                                        method <span class="text-danger">*</span></label>
                                                                                    <select name="payment_method"
                                                                                        class="form-control" required>
                                                                                        <option value="">select option
                                                                                        </option>
                                                                                        <option value="Cash">Cash
                                                                                        </option>
                                                                                        <option value="Bkash">Bkash
                                                                                        </option>
                                                                                        <option value="Rocket">Rocket
                                                                                        </option>
                                                                                        <option value="Nagad">Nagad
                                                                                        </option>
                                                                                        <option value="Bank">Bank</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="form-group mb-2">
                                                                                    <label for="">TrxId or
                                                                                        A/C</label>

                                                                                    <input type="text"
                                                                                        class="form-control" name="note"
                                                                                        placeholder="some other note">
                                                                                </div>
                                                                                <button type="submit"
                                                                                    class="btn btn-outline-primary btn-sm mr-2"
                                                                                    data-bs-toggle="modal"
                                                                                    data-bs-target="#exampleModal">
                                                                                    Save & Paid
                                                                                </button>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <form
                                                            action="{{ route('teacher.updateWithdrawalRequest', $item->id) }}"
                                                            method="post">
                                                            @csrf
                                                            <input type="hidden" name="type" value="Declined">
                                                            <button type="submit" class="btn btn-outline-danger btn-sm"
                                                                onclick="return confirm('Are you sure?')">
                                                                Decline
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        @endif
                                        <td>{{ $item->wallet->user->name . ' - ' . $item->wallet->user->phone ?? '' }}</td>
                                        @if (request()->ref === 'Paid')
                                            <td>{{ $item->payment_method ?? '---' }}</td>
                                            <td>{{ $item->note ?? '---' }}</td>
                                        @endif
                                        <td>{{ $item->amount }}</td>
                                        @if (request()->ref !== 'Pending')
                                            <td>{{ $item->updated_at->format('d F, Y h:i A') }}</td>
                                        @else
                                            <td>{{ $item->created_at->format('d F, Y h:i A') }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $data->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
