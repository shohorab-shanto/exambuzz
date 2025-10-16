<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use App\Models\RevisionSubject;
use App\Models\RevisionTopicQuestion;
use App\Models\RevisionTopicQuestionOption;
use App\Models\RevisionTopicSource;
use App\Models\Subject;
use App\Models\TopicSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RevisionTopicSourceController extends Controller
{
    public function index()
    {
        $topic_source = RevisionTopicSource::orderBy('created_at', 'desc')->get();

        return view('backend.revision-topic-source.index', compact('topic_source'));
    }

    public function create()
    {
        $subjects = RevisionSubject::all();

        return view('backend.revision-topic-source.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'topic'  => 'required',
        //     'source' => 'required',
        // ]);

        // if ($validator->fails()) {
        //     return back()->with('toast_error', $validator->messages()->all()[0])->withInput();
        // }

        foreach ($request->topic as $key => $topic) {


            if ($topic) {
                $topic = RevisionTopicSource::create([
                    'revision_subjects_id' => $request->subject_id,
                    'topic' => $topic,
                    'source' => $request->source[$key] ? $request->source[$key] : '',
                ]);
            }

        }

        return to_route('revision_topic.source.index')->withToastSuccess('New topic & source added successfully');
    }

    public function edit(RevisionTopicSource $topic_source)
    {
        $subjects = RevisionSubject::all();

        return view('backend.revision-topic-source.edit', compact('topic_source', 'subjects'));
    }

    public function update(Request $request, RevisionTopicSource $topic_source)
    {
        $validator = Validator::make($request->all(), [
            'topic' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->with('toast_error', $validator->messages()->all()[0])->withInput();
        }

        $topic_source->revision_subjects_id = $request->subject_id;
        $topic_source->topic = $request->topic;
        $topic_source->source = $request->source;
        $topic_source->save();

        return to_route('revision_topic.source.index')->withToastSuccess('Topic and source updated successfully!!');
    }

    public function delete(Request $request, RevisionTopicSource $topic_source)
    {
        $topic_source->delete();

        return to_route('revision_topic.source.index')->withToastSuccess('Topic and source deleted successfully!!');
    }

    public function mcqQuestion($topic_id)
    {

        $data = [];
        $data['subjects'] = RevisionTopicSource::where('id', $topic_id)->first()->subject;

        $data['topic'] = DB::table('revision_topic_sources')->where('id', $topic_id)->first();

        return view('backend.revision-topic-source.mcq-question', $data);
    }

    public function createOrUpdateMCQQuestion(Request $request)
    {

        DB::beginTransaction();

        try {
            $topic_id = $request->topic_id;
            $subject_id = $request->subject_id;

            if (isset($request->question_id) && count($request->question_id) > 0) {

                foreach ($request->question_id as $question_id) {
                    $update_question = RevisionTopicQuestion::find($question_id);
                    $update_question->revision_subjects_id = $subject_id;
                    $update_question->revision_topic_source_id = $topic_id;
                    $update_question->question_name = $request->input('question_name_' . $question_id);
                    $update_question->question_explanation = $request->input('question_explanation_' . $question_id);
                    $update_question->save();

                    $update_option = $request->input('question_option_name_' . $question_id);
                    DB::table('revision_topic_question_options')->where('revision_topic_question_id', $question_id)->delete();

                    if ($update_option) {

                        foreach ($update_option as $u_key => $option) {

                            if ($option) {

                                if ($request->input('question_option_' . $question_id) == $u_key) {
                                    $answer = 1;
                                } else {
                                    $answer = 0;
                                }

                                RevisionTopicQuestionOption::create([
                                    'revision_topic_question_id' => $update_question->id,
                                    'option' => $option,
                                    'is_answer' => $answer,
                                ]);
                            }

                        }

                    }

                }

            }

            if (isset($request->serial_number) && count($request->serial_number) > 0) {

                foreach ($request->serial_number as $key => $serial_number) {
                    $postfix = $request->input('question_option_name_' . $topic_id . $serial_number);

                    if ($request->question_name[$key] != null && $postfix != null) {
                        $question = RevisionTopicQuestion::create([
                            'revision_subjects_id' => $subject_id,
                            'revision_topic_source_id' => $topic_id,
                            'question_name' => $request->question_name[$key],
                            'question_explanation' => $request->question_explanation[$key],
                        ]);

                        if ($postfix != null) {

                            foreach ($postfix as $o_key => $option) {

                                if ($request->input('question_option_' . $topic_id . $serial_number) == $o_key) {
                                    $answer = 1;
                                } else {
                                    $answer = 0;
                                }

                                RevisionTopicQuestionOption::create([
                                    'revision_topic_question_id' => $question->id,
                                    'option' => $option ?? 'Not set yet',
                                    'is_answer' => $answer,
                                ]);

                            }

                        }

                    }

                }

            }

            DB::commit();

            $exam_question = RevisionTopicQuestion::where('revision_topic_source_id', $request->topic_id)->count();

            $exam_question_nubmer = $exam_question / 5;
            if (is_float($exam_question_nubmer)) {
                $page = round($exam_question_nubmer);
            } else {
                $page = $exam_question_nubmer + 1;
            }

            $url = '/revision-topic-source/mcq-question/' . $request->topic_id . '?ref=' . $request->ref . '&type=' . $request->type . '&__s=' . $request->__s . '&page=' . $page;
            return redirect($url);
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()->withToastError($th->getMessage());
        }

    }

    public function deleteQuestion($question_id)
    {
        $data = ExamQuestion::find($question_id);

        if ($data) {

            foreach ($data->questionOptions as $option) {
                $option->delete();
            }

            $data->delete();

            return response()->json(['status' => true]);
        } else {
            return response()->json(['status' => false]);
        }

    }

}
