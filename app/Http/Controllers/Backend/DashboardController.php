<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\FAQ;
use App\Models\Package;
use App\Models\PackageHistory;
use App\Models\User;
use App\Models\WalletHistory;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {

        $date = Carbon::parse(today());
        $previousMonth = $date->subMonth()->format('m');
        $year = $date->subMonth()->format('Y');

        $data = [];

        $data['total_user'] = User::where('type', 'user')->count();
        $data['previous_month_total_user'] = User::where('type', 'user')->whereYear('created_at', $year)->whereMonth('created_at', $previousMonth)->count();
        $data['total_teacher'] = User::where('type', 'teacher')->count();
        $data['total_pending_amount'] = WalletHistory::where('status', 'Pending')->sum('amount');
        $data['total_package_sell'] = PackageHistory::count();
        $data['total_package_sell_amount'] = PackageHistory::sum('amount');

        /**
         * get this month and previous 5 month data form user, package sell and amount
         *
         * the chart apex abr chart
         */
        $currentDate = new DateTime();
        $last6Months = [];
        $month_name = [];
        $user_count = [];
        $package_sell_count = [];
        $package_sell_amount = [];

        // Get the current month and year
        $currentMonthYear = $currentDate->format('Y-m');
        $month_name[] = $currentDate->format('F');
        $user_count[] = User::where('type', 'user')->whereYear('created_at', $currentDate->format('Y'))->whereMonth('created_at', $currentDate->format('m'))->count();
        $package_sell_count[] = PackageHistory::whereYear('created_at', $currentDate->format('Y'))->whereMonth('created_at', $currentDate->format('m'))->count();
        $package_sell_amount[] = PackageHistory::whereYear('created_at', $currentDate->format('Y'))->whereMonth('created_at', $currentDate->format('m'))->sum('amount');
        $last6Months[] = $currentMonthYear;

// Loop to get the last 5 months

        for ($i = 1; $i <= 5; $i++) {
            // Modify the date to go back one month at a time
            $currentDate->modify('-1 month');

            // Get the updated month and year
            $previousMonthYear = $currentDate->format('Y-m');

            $last6Months[] = $previousMonthYear;

            $month_name[] = $currentDate->format('F');
            $user_count[] = User::where('type', 'user')->whereYear('created_at', $currentDate->format('Y'))->whereMonth('created_at', $currentDate->format('m'))->count();
            $package_sell_count[] = PackageHistory::whereYear('created_at', $currentDate->format('Y'))->whereMonth('created_at', $currentDate->format('m'))->count();
            $package_sell_amount[] = PackageHistory::whereYear('created_at', $currentDate->format('Y'))->whereMonth('created_at', $currentDate->format('m'))->sum('amount');
        }

        $modified_amount = [];

        foreach ($package_sell_amount as $key => $amount) {
            $modified_amount[] = $amount > 0 ? $amount / 1000 : 0;
        }

        $data['month_name'] = implode(',', $month_name);
        $data['user_count'] = implode(',', $user_count);
        $data['package_sell_count'] = implode(',', $package_sell_count);
        $data['package_sell_amount'] = implode(',', $modified_amount);
        // dd($month_name);

        return view('backend.dashboard', $data);
    }

    public function logout()
    {
        auth()->guard('admin')->logout();

        return to_route('login')->withToastSuccess('Logout Successful!!');
    }

    public function students(Request $request)
    {
        $data = [];
        $student = User::where('type', 'user');

        if ($request->package_id && $request->package_id != 'no') {
            $student = $student->whereHas('packageHistory', function ($q) use ($request) {
                $q->where('package_id', $request->package_id);
            });
        } elseif ($request->package_id && $request->package_id == 'no') {
            $student = $student->withCount('packageHistory')->having('package_history_count', '=', 0);
        } else {
            $student = $student->withCount([
                'packageHistory',
            ]);
        }

        if ($request->registration_id) {
            $student = $student->where('registration_id', $request->registration_id);
        }

        $student = $student->paginate()->withQueryString();

        $data['student'] = $student;
        $data['packages'] = Package::latest()->get();

        return view('backend.student.index', $data);
    }

    public function changeStudentStatus($id)
    {
        $data = User::where('type', 'user')->where('id', $id)->first();

        if (!$data) {
            return back()->withToastError('Unregistered student');
        }

        $data->status = $data->status == 1 ? 0 : 1;
        $data->save();

        return back()->withToastSuccess('Student status change successfully');
    }

    public function showStudentDetails($id)
    {
        $data = User::where('id', $id)->where('type', 'user')->withCount([
            'packageHistory',
        ])->first();

        if (!$data) {
            return back()->withToastError('No student found');
        }

        return view('backend.student.show', compact('data'));
    }

    // studentPackageHistory
    public function studentPackageHistory($id)
    {
        $data = User::where('id', $id)->where('type', 'user')->with([
            'packageHistory',
        ])->first();

        if (!$data) {
            return back()->withToastError('No student found');
        }

        return view('backend.student.package-history', compact('data'));
    }

    //studentPackageHistoryDelete
    public function studentPackageHistoryDelete($id)
    {
        $data = PackageHistory::where('id', $id)->first();

        if (!$data) {
            return back()->withToastError('No student found');
        }

        $data->delete();

        return back()->withToastSuccess('Package history delete successfully');
    }

    public function studentRequest()
    {
        $request = FAQ::latest()->paginate(100);

        return view('backend.student-request', compact('request'));
    }

}
