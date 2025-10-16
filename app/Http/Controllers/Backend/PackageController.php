<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Notification;
use App\Models\Package;
use App\Models\PackageHistory;
use App\Models\User;
use App\Models\Written;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PackageController extends Controller
{
    public function save_active_package(Request $request)
    {
        $find_user = User::where('registration_id', $request->registration_id)->select('id', 'registration_id')->first();
        if (!isset($find_user)) {
            return to_route('packages.active_package')->withToastError('User Not Found');
        }


        $find_package = Package::where('id', $request->package_id)->select('id', 'amount')->first();
        if (!isset($find_package)) {
            return to_route('packages.active_package')->withToastError('Package Not Found');
        }


        $find_package_buy = PackageHistory::where('user_id', $find_user->id)->where('package_id', $find_package->id)->first();
        if (isset($find_package_buy)) {
            return to_route('packages.active_package')->withToastError('User already buy this package');
        }


        PackageHistory::create([
            'user_id' => $find_user->id,
            'package_id' => $find_package->id,
            'amount' => $find_package->amount,
            'giving_amount' => $request->amount,
            'transaction_id' => 'WEB-MANUALLY-BUY-' . time(),
        ]);

        return to_route('packages.active_package')->withToastSuccess('Package buy successfully');
    }

    public function active_package()
    {
        $data = [];
        $data['packages'] = Package::where('status', 1)
            ->select('id', 'name', 'amount')
            //latest first
            ->latest()
            ->get();

        return view('backend/package/active_package', $data);
    }

    public function index()
    {
        $data = [];
        $data['package'] = Package::withCount('packageHistory')->latest()->paginate(8);

        return view('backend/package/index', $data);
    }

    public function createOrEdit($id = null)
    {
        $data = [];

        if ($id) {
            $data['package'] = Package::find($id);
        } else {
            $data['package'] = null;
        }

        $data['bcs_preliminary'] = Exam::orderBy('published_at', 'desc')
            ->where('category', 'BCS')
            ->where('subcategory', 'Preliminary')
            ->get();
        $data['bcs_written'] = Written::orderBy('published_at', 'desc')
            ->where('category', 'BCS')
            ->where('subcategory', 'Written')
            ->get();

        $data['bank_preliminary'] = Exam::where('category', 'Bank')
            ->where('subcategory', 'Preliminary')
            ->orderBy('published_at', 'desc')
            ->get();
        $data['bank_written'] = Written::orderBy('published_at', 'desc')
            ->where('category', 'Bank')
            ->where('subcategory', 'Written')
            ->get();

        $data['primary_preliminary'] = Exam::orderBy('published_at', 'desc')
            ->where('category', 'Others')
            ->where('subcategory', 'Preliminary')
            ->where('childcategory', 'Primary')
            ->get();

        $data['grade_preliminary'] = Exam::orderBy('published_at', 'desc')
            ->where('category', 'Others')
            ->where('subcategory', 'Preliminary')
            ->where('childcategory', '11 to 20 Grade')
            ->get();
        $data['grade_written'] = Written::orderBy('published_at', 'desc')
            ->where('category', 'Others')
            ->where('subcategory', 'Written')
            ->where('childcategory', '11 to 20 Grade')
            ->get();

        $data['non_cader_preliminary'] = Exam::orderBy('published_at', 'desc')
            ->where('category', 'Others')
            ->where('subcategory', 'Preliminary')
            ->where('childcategory', 'Non-Cadre')
            ->get();

        $data['non_cader_written'] = Written::orderBy('published_at', 'desc')
            ->where('category', 'Others')
            ->where('subcategory', 'Written')
            ->where('childcategory', 'Non-Cadre')
            ->get();

        $data['petrobangla_preliminary'] = Exam::orderBy('published_at', 'desc')
            ->where('category', 'Others')
            ->where('subcategory', 'Preliminary')
            ->where('childcategory', 'Petrobangla')
            ->get();

        $data['petrobangla_written'] = Written::orderBy('published_at', 'desc')
            ->where('category', 'Others')
            ->where('subcategory', 'Written')
            ->where('childcategory', 'Petrobangla')
            ->get();

        $data['si_written'] = Written::orderBy('published_at', 'desc')
            ->where('category', 'Others')
            ->where('subcategory', 'Written')
            ->where('childcategory', 'si')
            ->get();

        $data['job_preliminary'] = Exam::orderBy('published_at', 'desc')
            ->where('category', 'Others')
            ->where('subcategory', 'Preliminary')
            ->where('childcategory', 'Job Solution')
            ->get();
        $data['job_written'] = Written::orderBy('published_at', 'desc')
            ->where('category', 'Others')
            ->where('subcategory', 'Written')
            ->where('childcategory', 'Job Solution')
            ->get();

        return view('backend/package/create', $data);
    }

    public function storeOrUpdate(Request $request, $id = null)
    {

// dd($request->all());

        if ($id) {
            $package = Package::find($id);

            // Handle banner image update
            if ($request->hasFile('banner_image')) {
                $banner_image_file = $request->file('banner_image');
                if ($banner_image_file) {
                    $banner_image_path = public_path($package->banner_image);
                    if (File::exists($banner_image_path)) {
                        File::delete($banner_image_path);
                    }

                    $banner_img_gen = hexdec(uniqid());
                    $banner_image_url = 'images/pac$package/';
                    $banner_image_ext = strtolower($banner_image_file->getClientOriginalExtension());
                    $banner_img_name = $banner_img_gen . '.' . $banner_image_ext;
                    $final_banner_name = $banner_image_url . $banner_img_gen . '.' . $banner_image_ext;

                    $banner_image_file->move($banner_image_url, $banner_img_name);
                    $package->banner_image = $final_banner_name;
                    $package->save();
                }
            }

            if ($request->hasFile('image')) {

                $image_file = $request->file('image');

                if ($image_file) {

                    $image_path = public_path($package->image);

                    if (File::exists($image_path)) {
                        File::delete($image_path);
                    }

                    $img_gen = hexdec(uniqid());
                    $image_url = 'images/pac$package/';
                    $image_ext = strtolower($image_file->getClientOriginalExtension());

                    $img_name = $img_gen . '.' . $image_ext;
                    $final_name1 = $image_url . $img_gen . '.' . $image_ext;

                    $image_file->move($image_url, $img_name);
                    $package->image = $final_name1;
                    $package->save();

                }

            }

            $package->update([
                'name' => $request->name,
                'details' => $request->details,
                'amount' => $request->amount,
                'permission' => $request->permission,
                'validity' => $request->validity,
                'status' => $request->status,
                'type' => $request->type,
                'published_at' => $request->published_at,
                'discount_amount' => $request->discount_amount,
            ]);

            return to_route('packages.index')->withToastSuccess('Package updated successfully');
        } else {

            // Handle banner image upload
            if ($request->hasFile('banner_image')) {
                $banner_image_file = $request->file('banner_image');
                if ($banner_image_file) {
                    $banner_img_gen = hexdec(uniqid());
                    $banner_image_url = 'images/package/';
                    $banner_image_ext = strtolower($banner_image_file->getClientOriginalExtension());
                    $banner_img_name = $banner_img_gen . '.' . $banner_image_ext;
                    $final_banner_name = $banner_image_url . $banner_img_gen . '.' . $banner_image_ext;

                    $banner_image_file->move($banner_image_url, $banner_img_name);
                }
            }

            if ($request->hasFile('image')) {

                $image_file = $request->file('image');

                if ($image_file) {

                    $img_gen = hexdec(uniqid());
                    $image_url = 'images/package/';
                    $image_ext = strtolower($image_file->getClientOriginalExtension());

                    $img_name = $img_gen . '.' . $image_ext;
                    $final_name1 = $image_url . $img_gen . '.' . $image_ext;

                    $image_file->move($image_url, $img_name);
                }

            }

            $package = Package::create([
                'name' => $request->name,
                'details' => $request->details,
                'amount' => $request->amount,
                'permission' => $request->permission,
                'validity' => $request->validity,
                'status' => $request->status,
                'type' => $request->type,
                'image' => $final_name1 ?? null,
                'banner_image' => $final_banner_name ?? null,
                'published_at' => $request->published_at,
                'discount_amount' => $request->discount_amount,
            ]);

            User::where('type', 'user')->chunk(200, function ($users) {
                foreach ($users as $user) {
                    if (isset($user->fcm_token)) {

                        FCMService::send(
                            $user->fcm_token,
                            [
                                'title' => "নতুন প্যাকেজ",
                                'body' => "নতুন একটি ব্যাচ চালু হয়েছে। আপনার প্রস্তুতি যাচাই করুন।",
                            ]
                        );

                        Notification::create([
                            'name' => 'নতুন প্যাকেজ',
                            'details' => "নতুন একটি ব্যাচ চালু হয়েছে। আপনার প্রস্তুতি যাচাই করুন।",
                            'user_id' => $user->id,
                            'to' => 'user',
                        ]);
                    }

                }

            });

            return back()->withToastSuccess('Package created successfully');
        }

    }

    public function destroy($id)
    {
        $package = Package::find($id);
        $package->delete();

        return back()->withToastSuccess('Package deleted successfully');
    }

}
