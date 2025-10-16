<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use App\Models\PreliminaryAnswer;
use App\Models\Subject;
use App\Models\Syllabus;
use App\Models\TopicSource;
use App\Models\Written;
use App\Models\WrittenQuestion;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ExamController extends Controller
{
    public function writtenMeritlist($exam_id)
    {
        $data = [];

        $data['find_exam'] = $find_exam = Exam::where('id', $exam_id)->select('id', 'pass_marks', 'expired_at')->first();

        $duplicate_entrys = PreliminaryAnswer::where('user_id', '<>', null)
            ->where('exam_id', $exam_id)
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();


        foreach ($duplicate_entrys as $item) {
            $entry_count = PreliminaryAnswer::where('user_id', $item->user_id)->where('exam_id', $exam_id)->get()->skip(1)->pluck('id');
            PreliminaryAnswer::whereIn('id', $entry_count)->delete();
        }

        $exam = PreliminaryAnswer::where('exam_id', $exam_id)->where('created_at', '<', $find_exam->expired_at)->orderBy('obtained_marks', 'desc');

        if (request()->registration_id) {
            $exam = $exam->whereHas('user', function ($q) {
                $q->where('registration_id', request()->registration_id);
            });
        }

        $exam = $exam->paginate()->withQueryString();

        $data['exam'] = $exam;

        if (count($exam) == 0) {
            return back()->withToastInfo('Still now for this exam no answer is submitted. So there is nothing to show any of student merit list.');
        }

        return view('backend.exam.meritlist', $data);
    }

    public function writtenMeritlistDownload($exam_id)
    {
        $data['find_exam'] = $find_exam = Exam::where('id', $exam_id)->select('id', 'pass_marks', 'expired_at')->first();

        $data['exam'] = PreliminaryAnswer::where('exam_id', $exam_id)->where('created_at', '<', $find_exam->expired_at)->orderBy('obtained_marks', 'desc')->get();

        $pdf = Pdf::loadView('backend.exam.meritlistdownload', $data);

        return $pdf->stream();
    }

    public function index()
    {
        $data = [];
        $exam = Exam::where('category', request()->ref)
            ->where('subcategory', request()->type);

        if (request()->child) {
            $exam = $exam->where('childcategory', request()->child);
        }

        if (request()->exam_type == 'archive') {
            $exam = $exam->whereDate('published_at', '<=', date('Y-m-d'));
        } elseif (request()->exam_type == 'upcoming') {
            $exam = $exam->whereDate('published_at', '>=', date('Y-m-d'));
        } elseif (request()->exam_type == 'live') {
            $exam = $exam->where('published_at', '<=', Carbon::now('Asia/Dhaka')->toDateTimeString())
                ->where('expired_at', '>=', Carbon::now('Asia/Dhaka')->toDateTimeString());
        }

        $exams = $exam->withCount('questions')->latest('published_at')
            ->paginate(20);
        $list = [];

//        foreach ($exam as $item) {
//            $item['subjects'] = Subject::whereIn('id', explode(',', $item->subject_id))->get();
//            $item['topics'] = TopicSource::whereIn('id', explode(',', $item->topic_id))->get();
//            $list[] = $item;
//        }

        $exams->getCollection()->transform(function ($item) {
            $item['subjects'] = Subject::whereIn('id', explode(',', $item->subject_id))->get();
            $item['topics'] = TopicSource::whereIn('id', explode(',', $item->topic_id))->get();
            return $item;
        });

        $data['exam'] = $exams;

        return view('backend.exam.index', $data);
    }

    public function create($exam_id = null)
    {
        $data = [];
        $data['subjects'] = Subject::all();

        if ($exam_id) {
            $data['exam'] = $e = Exam::find($exam_id);
            $data['topic_source'] = TopicSource::whereIn('subject_id', explode(',', $e->subject_id))->get();
        }

        return view('backend.exam.create', $data);
    }

    public function storeOrUpdate(Request $request, $exam_id = null)
    {

        if ($request->subject_id == null) {
            return back()->withToastError('Select at least one subject');
        }

        if ($request->topic_id == null) {
            return back()->withToastError('Select at least one subjects topic');
        }

        if (!$exam_id) {
            Exam::create([
                'name' => $request->name,
                'category' => $request->category,
                'subcategory' => $request->subcategory,
                'childcategory' => $request->childcategory,
                'subject_id' => implode(',', $request->subject_id),
                'topic_id' => implode(',', $request->topic_id),
                'per_question_positive_mark' => $request->per_question_positive_mark,
                'per_question_negative_mark' => $request->per_question_negative_mark,
                'published_at' => Carbon::parse($request->published_at),
                'expired_at' => Carbon::parse($request->expired_at),
                'duration' => ($request->duration * 60),
                'pass_marks' => $request->pass_marks,
                'comment' => $request->comment,
                'status' => $request->status,
            ]);
        } else {
            $exam = Exam::find($exam_id);
            $exam->name = $request->name;
            $exam->category = $request->category;
            $exam->subcategory = $request->subcategory;
            $exam->childcategory = $request->childcategory;
            $exam->subject_id = implode(',', $request->subject_id);
            $exam->topic_id = implode(',', $request->topic_id);
            $exam->per_question_positive_mark = $request->per_question_positive_mark;
            $exam->per_question_negative_mark = $request->per_question_negative_mark;
            $exam->published_at = Carbon::parse($request->published_at);
            $exam->expired_at = Carbon::parse($request->expired_at);
            $exam->duration = ($request->duration * 60);
            $exam->pass_marks = $request->pass_marks;
            $exam->comment = $request->comment;
            $exam->status = $request->status;
            $exam->save();
        }

        return back()->withToastSuccess('Exam created successfully');

    }

    public function mcqQuestion($exam_id)
    {

//        $subject_id = request()->__s;
//        $find_exam_question = ExamQuestion::where('exam_id', $exam_id)->where('subject_id', $subject_id)->with('questionOptions')->get();
//
//        $find_exam_question->each(function ($question) {
//            // Keep only the first 4 options and delete the rest
//            $question->questionOptions = $question->questionOptions->skip(4)->take(100);
//
//
//            // Delete the rest of the options
//            if ($question->questionOptions) {
//                $question->questionOptions->each(function ($option) {
//                    $option->delete();
//                });
//            }
//        });


        $data = [];
        $data['exam'] = $exam = Exam::where('id', $exam_id)->withCount('questions')->first();
        $data['subjects'] = Subject::select(['id', 'name'])
            ->whereIn('id', explode(',', $exam->subject_id))
            ->withCount([
                'exams' => function ($q) use ($exam) {
                    return $q->where('exam_id', $exam->id);
                },

            ])
            ->get();
        $data['s_s'] = Subject::select(['id', 'name'])
            ->where('id', request()->__s)
            ->with([
                'exams' => function ($q) use ($exam) {
                    return $q->where('exam_id', $exam->id)
                        ->select(['id', 'exam_id', 'subject_id', 'topic_id', 'question_name', 'question_explanation'])
                        ->with([
                            'questionOptions' => function ($option) {
                                return $option->select(['id', 'exam_question_id', 'option', 'is_answer']);
                            },

                        ]);
                },

            ])
            ->first();

        $data['topic'] = DB::table('topic_sources')
            ->whereIn('id', explode(',', $exam->topic_id))
            ->get();

        return view('backend/exam/mcq-question', $data);
    }

    public function createOrUpdateMCQQuestion(Request $request)
    {
        // dd($request->all());
        DB::beginTransaction();

        try {
            $subject_id = $request->subject_id;
            
            // Validate new questions have subject selected
            if (isset($request->serial_number) && count($request->serial_number) > 0) {
                if (!$request->has('subject_question_id')) {
                    return back()->withToastError('Please select a subject for each question');
                }
                
                foreach ($request->subject_question_id as $key => $subj_id) {
                    if (empty($subj_id)) {
                        return back()->withToastError('Subject is required for all questions');
                    }
                }
            }

            if (isset($request->question_id) && count($request->question_id) > 0) {

                foreach ($request->question_id as $question_id) {
                    $update_question = ExamQuestion::find($question_id);
                    $update_question->question_name = $request->input('question_name_' . $question_id);
                    $update_question->question_explanation = $request->input('question_explanation_' . $question_id);
                    
                    // Update subject and topic if provided
                    if ($request->has('subject_question_id_' . $question_id)) {
                        $update_question->subject_id = $request->input('subject_question_id_' . $question_id);
                    }
                    if ($request->has('topic_question_id_' . $question_id)) {
                        $update_question->topic_id = $request->input('topic_question_id_' . $question_id);
                    }
                    
                    $update_question->save();

                    $update_option = $request->input('question_option_name_' . $question_id);
                    DB::table('exam_question_options')->where('exam_question_id', $question_id)->delete();

                    if ($update_option) {

                        foreach ($update_option as $u_key => $option) {

                            if ($option) {

                                if ($request->input('question_option_' . $question_id) == $u_key) {
                                    $answer = 1;
                                } else {
                                    $answer = 0;
                                }

                                ExamQuestionOption::create([
                                    'exam_question_id' => $update_question->id,
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
                    // Get subject and topic for this specific question
                    $question_subject_id = isset($request->subject_question_id[$key]) ? $request->subject_question_id[$key] : $subject_id;
                    $question_topic_id = isset($request->topic_question_id[$key]) ? $request->topic_question_id[$key] : null;
                    
                    $postfix = $request->input('question_option_name_' . $question_subject_id . $serial_number);

                    if ($request->question_name[$key] != null && $postfix != null && $question_subject_id) {
                        $question = ExamQuestion::create([
                            'exam_id' => $request->exam_id,
                            'subject_id' => $question_subject_id,
                            'topic_id' => $question_topic_id,
                            'question_name' => $request->question_name[$key],
                            'question_explanation' => $request->question_explanation[$key],
                        ]);

                        if ($postfix != null) {

                            foreach ($postfix as $o_key => $option) {

                                if ($request->input('question_option_' . $question_subject_id . $serial_number) == $o_key) {
                                    $answer = 1;
                                } else {
                                    $answer = 0;
                                }

                                ExamQuestionOption::create([
                                    'exam_question_id' => $question->id,
                                    'option' => $option ?? 'Not set yet',
                                    'is_answer' => $answer,
                                ]);

                            }

                        }

                    }

                }

            }

            DB::commit();

            $exam_question = ExamQuestion::where('subject_id', $request->__s)->where('exam_id', $request->exam_id)->count();

            $exam_question_nubmer = $exam_question / 5;
            if (is_float($exam_question_nubmer)) {
                $page = round($exam_question_nubmer);
            } else {
                $page = $exam_question_nubmer + 1;
            }

            $url = '/exam/mcq-question/' . $request->exam_id . '?ref=' . $request->ref . '&type=' . $request->type . '&__s=' . $request->__s . '&page=' . $page;
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

    //written question from here
    public function written()
    {
        $data = [];
        $exam = Written::where('category', request()->ref)
            ->where('subcategory', request()->type);

        if (request()->child) {
            $exam = $exam->where('childcategory', request()->child);
        }

        if (request()->exam_type == 'archive') {
            $exam = $exam->whereDate('published_at', '<=', date('Y-m-d'));
        } elseif (request()->exam_type == 'upcoming') {
            $exam = $exam->whereDate('published_at', '>=', date('Y-m-d'));
        } elseif (request()->exam_type == 'live') {
            $exam = $exam->where('published_at', '<=', Carbon::now('Asia/Dhaka')->toDateTimeString())
                ->where('expired_at', '>=', Carbon::now('Asia/Dhaka')->toDateTimeString());
        }

        $exams = $exam->latest('published_at')
            ->paginate(20);
//        $list = [];
//
//        foreach ($exam as $item) {
//            $item['subjects'] = Subject::whereIn('id', explode(',', $item->subject_id))->get();
//            $item['topics'] = TopicSource::whereIn('id', explode(',', $item->topic_id))->get();
//            $list[] = $item;
//        }

        $exams->getCollection()->transform(function ($item) {
            $item['subjects'] = Subject::whereIn('id', explode(',', $item->subject_id))->get();
            $item['topics'] = TopicSource::whereIn('id', explode(',', $item->topic_id))->get();
            return $item;
        });

        $data['exam'] = $exams;

        return view('backend.exam.written', $data);
    }

    public function writtenCreate($exam_id = null)
    {
        $data = [];
        $data['subjects'] = Subject::all();

        if ($exam_id) {
            $data['exam'] = $e = Written::find($exam_id);
            $data['topic_source'] = TopicSource::whereIn('subject_id', explode(',', $e->subject_id))->get();
        }

        return view('backend.exam.written-create', $data);
    }

    public function writtenStoreOrUpdate(Request $request, $exam_id = null)
    {

        //dd($request->all());

        if ($request->subject_id == null) {
            return back()->withToastError('Select at least one subject');
        }

        if ($request->topic_id == null) {
            return back()->withToastError('Select at least one subjects topic');
        }

        if (!$exam_id) {

            if ($request->hasFile('question')) {

                $image_file = $request->file('question');

                if ($image_file) {

                    $img_gen = hexdec(uniqid());
                    $image_url = 'images/written/';
                    $image_ext = strtolower($image_file->getClientOriginalExtension());

                    $img_name = $img_gen . '.' . $image_ext;
                    $question = $image_url . $img_gen . '.' . $image_ext;

                    $image_file->move($image_url, $img_name);
                }

            }

            if ($request->hasFile('answer')) {

                $image_file = $request->file('answer');

                if ($image_file) {

                    $img_gen = hexdec(uniqid());
                    $image_url = 'images/written/';
                    $image_ext = strtolower($image_file->getClientOriginalExtension());

                    $img_name = $img_gen . '.' . $image_ext;
                    $answer = $image_url . $img_gen . '.' . $image_ext;

                    $image_file->move($image_url, $img_name);
                }

            }

            Written::create([
                'name' => $request->name,
                'category' => $request->category,
                'subcategory' => $request->subcategory,
                'childcategory' => $request->childcategory,
                'subject_id' => implode(',', $request->subject_id),
                'topic_id' => implode(',', $request->topic_id),
                'published_at' => $request->published_at,
                'expired_at' => $request->expired_at,
                'pass_marks' => $request->pass_marks,
                'status' => $request->status,
                'duration' => ($request->duration * 60),
                'question' => $question ?? '',
                'answer' => $answer ?? '',
                'result_published' => $request->result_published,
                'paper_published' => $request->paper_published
            ]);
        } else {
            $exam = Written::find($exam_id);
            $exam->name = $request->name;
            $exam->category = $request->category;
            $exam->subcategory = $request->subcategory;
            $exam->childcategory = $request->childcategory;
            $exam->subject_id = implode(',', $request->subject_id);
            $exam->topic_id = implode(',', $request->topic_id);
            $exam->published_at = Carbon::parse($request->published_at);
            $exam->expired_at = $request->expired_at;
            $exam->pass_marks = $request->pass_marks;
            $exam->status = $request->status;
            $exam->duration = ($request->duration * 60);
            $exam->result_published = $request->result_published;
            $exam->paper_published = $request->paper_published;
            $exam->save();

            if ($request->hasFile('question')) {

                $image_file = $request->file('question');

                if ($image_file) {

                    $image_path = public_path($exam->answer);

                    if (File::exists($image_path)) {
                        File::delete($image_path);
                    }

                    $img_gen = hexdec(uniqid());
                    $image_url = 'images/written/';
                    $image_ext = strtolower($image_file->getClientOriginalExtension());

                    $img_name = $img_gen . '.' . $image_ext;
                    $question = $image_url . $img_gen . '.' . $image_ext;

                    $image_file->move($image_url, $img_name);

                    $exam->question = $question;
                    $exam->save();
                }

            }

            if ($request->hasFile('answer')) {

                $image_file = $request->file('answer');

                if ($image_file) {

                    $image_path = public_path($exam->answer);

                    if (File::exists($image_path)) {
                        File::delete($image_path);
                    }

                    $img_gen = hexdec(uniqid());
                    $image_url = 'images/written/';
                    $image_ext = strtolower($image_file->getClientOriginalExtension());

                    $img_name = $img_gen . '.' . $image_ext;
                    $answer = $image_url . $img_gen . '.' . $image_ext;

                    $image_file->move($image_url, $img_name);

                    $exam->answer = $answer;
                    $exam->save();
                }

            }

        }

        return back()->withToastSuccess('Exam created successfully');

    }

    public function writtenQuestion($exam_id)
    {
        $data = [];
        $data['exam'] = $exam = Written::where('id', $exam_id)->first();

        return view('backend.exam.written-question', $data);
    }

    public function createOrUpdateWrittenQuestion(Request $request)
    {
        // dd($request->all());
        DB::beginTransaction();

        try {
            $subject_id = $request->subject_id;

            if (isset($request->question_id) && count($request->question_id) > 0) {

                foreach ($request->question_id as $question_id) {
                    $update_question = WrittenQuestion::find($question_id);
                    $update_question->name = $request->input('question_name_' . $question_id);
                    $update_question->mark = $request->input('question_mark_' . $question_id);
                    $update_question->save();

                }

            }

            if (isset($request->serial_number) && count($request->serial_number) > 0) {

                foreach ($request->serial_number as $key => $serial_number) {
                    WrittenQuestion::create([
                        'written_id' => $request->written_id,
                        'subject_id' => $subject_id,
                        'name' => $request->question_name[$key],
                        'mark' => $request->question_mark[$key],
                    ]);

                }

            }

            DB::commit();

            return back()->withToastSuccess('Question updated or created successfully');

        } catch (\Throwable $th) {
            DB::rollBack();

            return back()->withToastError($th->getMessage());
        }

    }

    public function deleteWrittenQuestion($question_id)
    {
        $data = WrittenQuestion::find($question_id);

        if ($data) {

            $data->delete();

            return response()->json(['status' => true]);
        } else {
            return response()->json(['status' => false]);
        }

    }

    //syllabus
    public function syllabus()
    {
        $data = Syllabus::where('category', request()->ref)
            ->where('subcategory', request()->type);

        if (request()->child) {
            $data = $data->where('childcategory', request()->child);
        }

        $data = $data->first();

        return view('backend.exam.syllabus', compact('data'));
    }

    public function uploadSyllabus(Request $request)
    {

        $data = Syllabus::where('category', $request->category)
            ->where('subcategory', $request->subcategory)
            ->where('childcategory', $request->childcategory)
            ->first();

        if ($request->hasFile('syllabus')) {

            $image_file = $request->file('syllabus');

            if ($image_file) {

                if ($data) {
                    $image_path = public_path($data->syllabus);

                    if (File::exists($image_path)) {
                        File::delete($image_path);
                    }

                }

                $img_gen = hexdec(uniqid());
                $image_url = 'images/syllabus/';
                $image_ext = strtolower($image_file->getClientOriginalExtension());

                $img_name = $img_gen . '.' . $image_ext;
                $syllabus = $image_url . $img_gen . '.' . $image_ext;

                $image_file->move($image_url, $img_name);
            }

        }

        Syllabus::updateOrCreate(
            [
                'id' => $data->id ?? null,
            ],
            [
                'category' => $request->category,
                'subcategory' => $request->subcategory,
                'childcategory' => $request->childcategory,
                'syllabus' => $syllabus ?? '',
            ]);

        return back()->withToastSuccess('Syllabus uploaded successfully');
    }

    //ajax response
    public function getTopic(Request $request)
    {
        $data = TopicSource::whereIn('subject_id', $request->subjects)->get();

        return json_encode($data);
    }

}
