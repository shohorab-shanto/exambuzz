<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class AdminManagementController extends Controller {
    public function index() {
        $admin = Admin::all();

        return view('backend.admin.index', compact('admin'));
    }

    public function create() {
        return view('backend.admin.create');
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'phone'    => 'required|numeric',
            'email'    => 'required|email|unique:admins',
            'password' => 'required|min:8',
            'image'    => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        if ($validator->fails()) {
            return back()->with('toast_error', $validator->messages()->all())->withInput();
        }

        if ($request->hasFile('image')) {

            $image_file = $request->file('image');

            if ($image_file) {

                $img_gen   = hexdec(uniqid());
                $image_url = 'images/admin/';
                $image_ext = strtolower($image_file->getClientOriginalExtension());

                $img_name    = $img_gen . '.' . $image_ext;
                $final_name1 = $image_url . $img_gen . '.' . $image_ext;

                $image_file->move($image_url, $img_name);
            }

        }

        $admin           = new Admin();
        $admin->name     = $request->name;
        $admin->phone    = $request->phone;
        $admin->email    = $request->email;
        $admin->password = bcrypt($request->password);
        $admin->address  = $request->address;
        $admin->image    = $final_name1 ?? '';

// $admin->users    = $request->users;

// $admin->course   = $request->course;

// $admin->exam     = $request->exam;
        // $admin->general  = $request->general;
        $admin->status = 1;
        $admin->save();

        return to_route('admin.index')->withToastSuccess('New admin registered successfully!!');
    }

    public function edit(Admin $admin) {
        return view('backend.admin.edit', compact('admin'));
    }

    public function update(Request $request, Admin $admin) {
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'phone'    => 'required',
            'email'    => 'required|email|unique:admins,email,' . $admin->id,
            'password' => 'nullable|min:8',
            'image'    => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        if ($validator->fails()) {
            return back()->with('toast_error', $validator->messages()->all())->withInput();
        }

        if ($request->hasFile('image')) {

            $image_file = $request->file('image');

            if ($image_file) {

                $image_path = public_path($admin->image);

                if (File::exists($image_path)) {
                    File::delete($image_path);
                }

                $img_gen   = hexdec(uniqid());
                $image_url = 'images/admin/';
                $image_ext = strtolower($image_file->getClientOriginalExtension());

                $img_name    = $img_gen . '.' . $image_ext;
                $final_name1 = $image_url . $img_gen . '.' . $image_ext;

                $image_file->move($image_url, $img_name);
                $admin->image = $final_name1;
                $admin->save();

            }

        }

        if ($request->password) {
            $admin->password = bcrypt($request->password);
            $admin->save();
        }

        $admin->name    = $request->name;
        $admin->phone   = $request->phone;
        $admin->email   = $request->email;
        $admin->address = $request->address;

// $admin->users   = $request->users;

// $admin->course  = $request->course;

// $admin->exam    = $request->exam;
        // $admin->general = $request->general;
        $admin->save();

        return redirect()->route('admin.index')->withToastSuccess('The admin updated successfully!!');
    }

    public function activeAdmin(Request $request, Admin $admin) {
        $admin->status = 1;
        $admin->save();

        return redirect()->back()->withToastSuccess('The admin activated successfully!!');
    }

    public function inactiveAdmin(Request $request, Admin $admin) {
        $admin->status = 0;
        $admin->save();

        return redirect()->back()->withToastSuccess('The admin inactivated successfully!!');
    }

    public function delete(Request $request, Admin $admin) {
        $image_path = public_path($admin->image);

        if (File::exists($image_path)) {
            File::delete($image_path);
        }

        $admin->delete();

        return redirect()->back()->withToastSuccess('The admin deleted successfully!!');
    }

}
