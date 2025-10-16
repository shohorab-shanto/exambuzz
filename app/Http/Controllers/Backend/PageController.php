<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller {
    public function index() {
        $pages = Page::all();

        return view('backend.page.index', compact('pages'));
    }

    public function create() {
        return view('backend.page.create');
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|unique:pages',
            'details' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->with('toast_error', $validator->messages()->all()[0])->withInput();
        }

        Page::create([
            'name'    => $request->name,
            'details' => $request->details,
        ]);

        return to_route('page.index')->withToastSuccess('New page added successfully');
    }

    public function edit(Page $page) {
        return view('backend.page.edit', compact('page'));
    }

    public function update(Request $request, Page $page) {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|unique:pages,name,' . $page->id,
            'details' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->with('toast_error', $validator->messages()->all()[0])->withInput();
        }

        if (!in_array($page->id, ['1', '3'])) {

            $page->name = $request->name;
        }

        $page->details = $request->details;
        $page->save();

        return to_route('page.index')->withToastSuccess('Page updated successfully!!');
    }

    public function delete(Request $request, Page $page) {
        $page->delete();

        return to_route('page.index')->withToastSuccess('Page deleted successfully!!');
    }

}
