<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubjectController extends Controller
{
    // getSubjects
    public function getSubjects(Request $request)
    {
        $subjects = Subject::select('id', 'name')->get();

        return response()->json($subjects);
    }

    public function index() {
        $subject = Subject::all();

        return view('backend.subject.index', compact('subject'));
    }

    public function create() {
        return view('backend.subject.create');
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|unique:subjects',
        ]);

        if ($validator->fails()) {
            return back()->with('toast_error', $validator->messages()->all()[0])->withInput();
        }

        Subject::create([
            'name'    => $request->name,
        ]);

        return to_route('subject.index')->withToastSuccess('New subject added successfully');
    }

    public function edit(Subject $subject) {
        return view('backend.subject.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject) {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|unique:subjects,name,' . $subject->id,
        ]);

        if ($validator->fails()) {
            return back()->with('toast_error', $validator->messages()->all()[0])->withInput();
        }

        $subject->name    = $request->name;
        $subject->save();

        return to_route('subject.index')->withToastSuccess('Subject updated successfully!!');
    }

    public function delete(Request $request, Subject $subject) {
        $subject->delete();

        return to_route('subject.index')->withToastSuccess('Subject deleted successfully!!');
    }
}
