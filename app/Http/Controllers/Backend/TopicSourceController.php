<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\TopicSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TopicSourceController extends Controller {
    public function index() {
        $topic_source = TopicSource::orderBy('created_at', 'desc')->get();

        return view('backend.topic-source.index', compact('topic_source'));
    }

    public function create() {
        $subjects = Subject::all();

        return view('backend.topic-source.create', compact('subjects'));
    }

    public function store(Request $request) {
        // $validator = Validator::make($request->all(), [
        //     'topic'  => 'required',
        //     'source' => 'required',
        // ]);

        // if ($validator->fails()) {
        //     return back()->with('toast_error', $validator->messages()->all()[0])->withInput();
        // }

        foreach ($request->topic as $key => $topic) {


            if ($topic) {
                $topic = TopicSource::create([
                    'subject_id' => $request->subject_id,
                    'topic'      => $topic,
                    'source'     =>  $request->source[$key] ? $request->source[$key] : '',
                ]);
            }

        }

        return to_route('topic.source.index')->withToastSuccess('New topic & source added successfully');
    }

    public function edit(TopicSource $topic_source) {
        $subjects = Subject::all();

        return view('backend.topic-source.edit', compact('topic_source', 'subjects'));
    }

    public function update(Request $request, TopicSource $topic_source) {
        $validator = Validator::make($request->all(), [
            'topic'  => 'required',
        ]);

        if ($validator->fails()) {
            return back()->with('toast_error', $validator->messages()->all()[0])->withInput();
        }

        $topic_source->subject_id = $request->subject_id;
        $topic_source->topic      = $request->topic;
        $topic_source->source     = $request->source;
        $topic_source->save();

        return to_route('topic.source.index')->withToastSuccess('Topic and source updated successfully!!');
    }

    public function delete(Request $request, TopicSource $topic_source) {
        $topic_source->delete();

        return to_route('topic.source.index')->withToastSuccess('Topic and source deleted successfully!!');
    }

}
