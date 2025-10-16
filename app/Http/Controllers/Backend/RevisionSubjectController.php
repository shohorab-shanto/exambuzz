<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RevisionSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RevisionSubjectController extends Controller
{
    public function index()
    {
        $subject = RevisionSubject::all();

        return view('backend.revision_subject.index', compact('subject'));
    }

    public function create()
    {
        return view('backend.revision_subject.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:subjects',
        ]);

        if ($validator->fails()) {
            return back()->with('toast_error', $validator->messages()->all()[0])->withInput();
        }

        RevisionSubject::create([
            'name' => $request->name,
        ]);

        return to_route('revision_subject.index')->withToastSuccess('New subject added successfully');
    }

    public function edit(RevisionSubject $subject)
    {
        return view('backend.revision_subject.edit', compact('subject'));
    }

    public function update(Request $request, RevisionSubject $subject)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:subjects,name,' . $subject->id,
        ]);

        if ($validator->fails()) {
            return back()->with('toast_error', $validator->messages()->all()[0])->withInput();
        }

        $subject->name = $request->name;
        $subject->save();

        return to_route('revision_subject.index')->withToastSuccess('RevisionSubject updated successfully!!');
    }

    public function delete(Request $request, RevisionSubject $subject)
    {
        $subject->delete();

        return to_route('revision_subject.index')->withToastSuccess('RevisionSubject deleted successfully!!');
    }
}
