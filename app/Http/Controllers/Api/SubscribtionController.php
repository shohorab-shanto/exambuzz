<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscribtionController extends Controller
{
    public function packages()
    {
        $data = [];
        $data['course_base'] = Package::withCount('enrollStudentsCount')->where('type', 1)->get();
        $data['exam_base'] = Package::withCount('enrollStudentsCount')->where('type', 2)->latest()->paginate();

        return $this->successMessage('', $data);

    }

    public function purchasePackage(Request $request)
    {
        PackageHistory::create([
            'user_id' => $request->user_id,
            'package_id' => $request->package_id,
            'amount' => $request->amount,
            'transaction_id' => $request->transaction_id,
            'payment_method' => $request->payment_method,
            'payment_method_identity' => $request->payment_method_identity,

            'pg_transaction_id' => $request->pg_transaction_id,
            'card_type' => $request->card_type,
            'bank_transaction_id' => $request->bank_transaction_id,
            'payment_processes' => $request->payment_processes,
            'approval_code' => $request->approval_code,


        ]);

        return $this->successMessage();
    }

    public function packageHistory(Request $request)
    {
        $data = [];
        $data['present_subscribtion'] = PackageHistory::where('user_id', $request->user_id)->latest()->with('user', 'package')->first();
        $data['subscribtion_history'] = PackageHistory::where('user_id', $request->user_id)->latest()->with('user', 'package')->paginate();

        return $this->successMessage('', $data);
    }

    public function packageHistoryv2(Request $request)
    {
        $user_id = Auth::user()->id ?? $request->user_id;
        $data = [];
        $data['present_subscribtion'] = PackageHistory::where('user_id', $user_id)->latest()->with('user', 'package')->first();
        $data['subscribtion_history'] = PackageHistory::where('user_id', $user_id)->latest()->with('user', 'package')->paginate();

        return $this->successMessage('', $data);
    }
}
