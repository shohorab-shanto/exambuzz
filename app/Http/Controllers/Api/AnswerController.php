<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\Helper;
use App\Models\PreliminaryAnswer;
use App\Models\Subject;
use App\Models\TopicSource;
use App\Models\WrittenAnswer;
use App\Models\WrittenAnswerQuestion;
use App\Models\WrittenAnswerQuestionScript;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AnswerController extends Controller
{
    public function storePreliminaryAnswer(Request $request)
    {

        DB::beginTransaction();

        try {

            if (PreliminaryAnswer::where('user_id', Auth::id())->where('exam_id', $request->exam_id)->exists()) {
                return $this->errorMessage('This answer has been taken before');
            }

            $answer = new PreliminaryAnswer();
            $answer->user_id = Auth::id();
            $answer->exam_id = $request->exam_id;
            $answer->type = $request->type;
            $answer->answer = $request->answer;
            $answer->save();

            //claculating exam result
            $total_question = (int)$request->total_question;
            $positive_count = 0;
            $negative_count = 0;
            $empty_count = 0;

            $empty_marks = 0;

            $root_answer = str_replace("A", 0, $answer->answer);
            $root_answer = str_replace("B", 1, $root_answer);
            $root_answer = str_replace("C", 2, $root_answer);
            $root_answer = json_decode(str_replace("D", 3, $root_answer));

            $questions = ExamQuestion::where('exam_id', $request->exam_id)->limit($total_question)->with('questionOptions', 'subject', 'topic')->get();

            foreach ($questions as $key => $item) {

                if ($item->questionOptions->count() > 0) {

                    foreach ($item->questionOptions as $ie_key => $ie) {

                        if ($ie->is_answer == 1) {

                            if ($ie_key == $root_answer->$key) {
                                ++$positive_count;

                                $q = ExamQuestion::find($ie->exam_question_id);
                                $q->update([
                                    'correct' => $q->correct + 1,
                                    'total' => $q->total + 1,
                                ]);
                            } elseif ($root_answer->$key == '') {
                                ++$empty_count;
                                ++$empty_marks;

                                $q = ExamQuestion::find($ie->exam_question_id);
                                $q->update([
                                    'empty' => $q->empty + 1,
                                    'total' => $q->total + 1,
                                ]);
                            } else {
                                ++$negative_count;

                                $q = ExamQuestion::find($ie->exam_question_id);
                                $q->update([
                                    'negative' => $q->negative + 1,
                                    'total' => $q->total + 1,
                                ]);
                            }

                            break;

                        }

                    }

                } else {
                    ++$empty_count;
                    ++$empty_marks;
                }

            }

            $exam_details = Exam::where('id', $answer->exam_id)->first();

            $obtained_mark = $positive_count * $exam_details->per_question_positive_mark - $negative_count * $exam_details->per_question_negative_mark;

            $answer->obtained_marks = $obtained_mark;

            $answer->positive_count = $positive_count;
            $answer->negative_count = $negative_count;
            $answer->empty_count = $empty_count;

            $answer->positive_marks = $positive_count * $exam_details->per_question_positive_mark;
            $answer->negative_marks = $negative_count * $exam_details->per_question_negative_mark;
            $answer->empty_marks = $empty_marks;

            $answer->result_status = $obtained_mark >= $exam_details->pass_marks ? 1 : 0;
            $answer->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Your answer has been submitted',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $th,
            ]);
        }

    }

    public function showPreliminaryAnswer(Request $request)
    {
        $data = [];

        if ($request->exam_id) {

            $find_exam = Exam::where('id', $request->exam_id)->select('id', 'pass_marks', 'expired_at')->first();

            if (!PreliminaryAnswer::where('user_id', Auth::id())->where('exam_id', $request->exam_id)->exists()) {
                return $this->errorMessage('Somthing went wrong', '');
            }

            $answer = PreliminaryAnswer::where('user_id', Auth::id())
                ->where('exam_id', $request->exam_id)
                ->with(
                    'user',
                    'exam'
                )
                ->first();

            $data['question_count'] = ExamQuestion::where('exam_id', $answer->exam->id)->count();

            $data['total_examinee'] = PreliminaryAnswer::where('exam_id', $request->exam_id)->where('created_at', '<', $find_exam->expired_at)->count();


            $data['total_passed_examinee'] = PreliminaryAnswer::where('exam_id', $request->exam_id)
                ->where('obtained_marks', '>=', $answer->exam->pass_marks)
                ->where('created_at', '<', $find_exam->expired_at)
                ->count();

            $get_exam_answer = PreliminaryAnswer::where('exam_id', $answer->exam_id)
                ->where('created_at', '<', $find_exam->expired_at)
                ->orderBy('obtained_marks', 'desc')
                ->get();

            $data['my_position'] = Helper::FindMyPosition(Auth::id(), $get_exam_answer);

            $data['subjects'] = Subject::whereIn('id', explode(',', $answer->exam->subject_id))->get();
            $data['sources'] = TopicSource::whereIn('id', explode(',', $answer->exam->topic_id))->get();

            $data['answer'] = $answer;

        } elseif ($request->written_id) {

            if (!WrittenAnswer::where('user_id', Auth::id())->where('written_id', $request->written_id)->exists()) {
                return $this->errorMessage('Somthing went wrong', '');
            }

            $answer = WrittenAnswer::where('user_id', Auth::id())
                ->where('written_id', $request->written_id)
                ->with(
                    'written',
                    'user',
                    'teacher',
                    'writtenAnswerQuestion.writtenAnswerQuestion',
                    'writtenAnswerQuestion.writtenAnswerQuestionScript',
                )
                ->first();

            if ($answer->is_checked == 0) {
                return $this->errorMessage('Your script is under examine.');
            }

            $data['total_examinee'] = WrittenAnswer::where('written_id', $request->written_id)->count();

            $data['total_passed_examinee'] = WrittenAnswer::where('written_id', $request->written_id)->where('obtained_mark', '>', $answer->written->pass_marks)->count();

            $get_exam_answer = WrittenAnswer::where('written_id', $answer->written_id)->orderBy('obtained_mark', 'desc')->pluck('user_id')->toArray();
            $data['top_3'] = WrittenAnswer::where('written_id', $answer->written_id)
                ->with(['user', 'writtenAnswerQuestion.writtenAnswerQuestionScript', 'writtenAnswerQuestion.writtenAnswerQuestion'])
                ->orderBy('obtained_mark', 'desc')->limit(3)->get();

            $data['my_position'] = array_search(Auth::id(), $get_exam_answer) + 1;
            $data['answer'] = $answer;
            $data['subjects'] = Subject::whereIn('id', explode(',', $answer->written->subject_id))->get();
            $data['sources'] = TopicSource::whereIn('id', explode(',', $answer->written->topic_id))->get();
        }

        return $this->successMessage('ok', $data);
    }

    public function preliminaryAnswerScript(Request $request)
    {

        if (!PreliminaryAnswer::where('user_id', Auth::id())->where('exam_id', $request->exam_id)->exists()) {
            return $this->errorMessage('Somthing went wrong11', '');
        }

        $answer = PreliminaryAnswer::where('user_id', Auth::id())
            ->where('exam_id', $request->exam_id)
            ->with(
                'user',
                'exam',
            )
            ->first();

        $total_question = (int)$request->total_question;

        $root_answer = (str_replace("A", 0, $answer->answer));
        $root_answer = (str_replace("B", 1, $root_answer));
        $root_answer = (str_replace("C", 2, $root_answer));
        $root_answer = json_decode(str_replace("D", 3, $root_answer));

        $questions = ExamQuestion::where('exam_id', $request->exam_id)->limit($total_question)->with('questionOptions', 'subject', 'topic')->get();

        foreach ($questions as $key => $item) {

            if ($item->questionOptions->count() > 0) {

                foreach ($item->questionOptions as $ie_key => $ie) {

                    if ($ie->is_answer == 1) {

                        if ($ie_key == $root_answer->$key) {
                            $item['is_correct'] = 1;
                            $item['given_answer'] = $root_answer->$key;
                        } elseif ($root_answer->$key == '') {
                            $item['is_correct'] = 2;
                            // $item['given_answer'] = $root_answer->$key;
                            $item['given_answer'] = '';
                        } else {
                            $item['is_correct'] = 3;
                            $item['given_answer'] = $root_answer->$key;
                        }

                        break;

                    }

                }

            } else {
                $item['is_correct'] = 2;
                $item['given_answer'] = '';
            }

        }

        return $this->successMessage('ok', $questions);
    }

    public function preliminaryAnswerMeritListv2(Request $request)
    {
        if (isset($request->exam_id)) {

            $find_exam = Exam::where('id', $request->exam_id)->select('id', 'pass_marks', 'expired_at')->first();

            $get_exam_answer = PreliminaryAnswer::where('exam_id', $request->exam_id)
                ->leftJoin('users', function ($join) {
                    $join->on('preliminary_answers.user_id', '=', 'users.id');
                })
                ->select(
                    'preliminary_answers.id',
                    'preliminary_answers.user_id',
                    'preliminary_answers.obtained_marks',
                    'users.name as user_name'
                )
                ->where('preliminary_answers.created_at', '<', $find_exam->expired_at)
                ->orderBy('preliminary_answers.obtained_marks', 'desc')->get();

            $get_exam_answer = Helper::SetPosition($get_exam_answer);
            $perPage = 15;
            $page = request()->get('page', 1);
            $get_exam_answer = Helper::CustomPaginate($perPage, $page, $get_exam_answer, $request->search);

            return $this->successMessage('okasdf', $get_exam_answer);

        } else {
            $get_exam_answer = WrittenAnswer::where('written_id', $request->written_id)
                ->where('is_checked', 1)
                ->leftJoin('users', function ($join) {
                    $join->on('written_answers.user_id', '=', 'users.id');
                })
                ->select(
                    'written_answers.id',
                    'written_answers.user_id',
                    'written_answers.obtained_mark as obtained_marks',
                    'users.name as user_name'
                )
                ->orderBy('written_answers.obtained_mark', 'desc')->get();


            $get_exam_answer = Helper::SetPosition($get_exam_answer);

            $perPage = 15;
            $page = request()->get('page', 1);
            $get_exam_answer = Helper::CustomPaginate($perPage, $page, $get_exam_answer, $request->search);


        }

        return $this->successMessage('ok32132', $get_exam_answer);
    }

    public function preliminaryAnswerMeritList(Request $request)
    {

        if (isset($request->exam_id)) {

            $find_exam = Exam::where('id', $request->exam_id)->select('id', 'pass_marks', 'expired_at')->first();

            $get_exam_answer = PreliminaryAnswer::where('exam_id', $request->exam_id)
                ->select(['id', 'user_id', 'obtained_marks'])
                ->where('created_at', '<', $find_exam->expired_at)
                ->orderBy('obtained_marks', 'desc');

            if ($request->search) {
                $get_exam_answer = $get_exam_answer->whereHas('user', function ($q) use ($request) {
                    return $q->where('name', 'LIKE', '%' . $request->search . '%');
                });
            }

            $get_exam_answer = $get_exam_answer->with(['user' => function ($q) {
                return $q->select(['id', 'name']);
            },
            ])->get();

            $get_exam_answer = Helper::SetPosition($get_exam_answer);
            $perPage = 15;
            $page = request()->get('page', 1);
            $get_exam_answer = Helper::CustomPaginate($perPage, $page, $get_exam_answer);

            return $this->successMessage('okasdf', $get_exam_answer);

        } else {
            $get_exam_answer = WrittenAnswer::where('written_id', $request->written_id)
                ->where('is_checked', 1)
                ->select(['id', 'user_id', 'obtained_mark'])
                ->orderBy('obtained_mark', 'desc');

            if ($request->search) {
                $get_exam_answer = $get_exam_answer->whereHas('user', function ($q) use ($request) {
                    return $q->where('name', 'LIKE', '%' . $request->search . '%');
                });
            }

            $get_exam_answer = $get_exam_answer->with(['user' => function ($q) {
                return $q->select(['id', 'name']);
            },
            ])->paginate();

            $get_exam_answer_position = WrittenAnswer::where('written_id', $request->written_id)
                ->where('is_checked', 1)
                ->select(['id', 'user_id', 'obtained_mark'])
                ->orderBy('obtained_mark', 'desc')
                ->pluck('user_id')
                ->toArray();

            foreach ($get_exam_answer as $key => $item) {

                $item['position'] = array_search($item->user_id, $get_exam_answer_position) + 1;
            }

        }

        return $this->successMessage('ok', $get_exam_answer);
    }

    public function storeWrittenAnswerv2(Request $request)
    {
        DB::beginTransaction();

        try {
            $auth_user_id = Auth::user()->id;


            $find_written_answer = WrittenAnswer::where('user_id', $auth_user_id)->where('written_id', $request->written_id)->first();

            if (isset($find_written_answer)) {
                if ($find_written_answer->deleted_at) {
                    $find_written_answer->delete();
                }
            }

            $find_written_answer = WrittenAnswer::where('user_id', $auth_user_id)->where('written_id', $request->written_id)->first();


            if (isset($find_written_answer)) {

                foreach (json_decode($request->question_id) as $question) {

                    $answer_question = WrittenAnswerQuestion::where('written_answer_id', $find_written_answer->id)
                        ->where('written_question_id', $question)
                        ->first();

                    if (!isset($answer_question)) {
                        $answer_question = WrittenAnswerQuestion::create([
                            'written_answer_id' => $find_written_answer->id,
                            'written_question_id' => $question,
                        ]);
                    }

                    $files = [];

                    $request_file_name = 'student_script_' . $question;

                    if ($request->hasfile($request_file_name)) {
                        foreach ($request->file($request_file_name) as $file) {
                            $name = $question . '-' . $answer_question->written_answer_id . Str::uuid() . '.' . $file->extension();
                            $file->move(public_path('images/script/'), $name);
                            $files[] = 'images/script/' . $name;
                        }
                    }

                    foreach ($files as $f) {
                        WrittenAnswerQuestionScript::create([
                            'written_answer_question_id' => $answer_question->id,
                            'student_script' => $f,
                        ]);
                    }

                }

            } else {
                $answer = WrittenAnswer::create([
                    'user_id' => $auth_user_id,
                    'written_id' => $request->written_id,
                    'category' => $request->category,
                ]);

                foreach (json_decode($request->question_id) as $question) {
                    $answer_question = WrittenAnswerQuestion::create([
                        'written_answer_id' => $answer->id,
                        'written_question_id' => $question,
                    ]);

                    $files = [];

                    $request_file_name = 'student_script_' . $question;

                    if ($request->hasfile($request_file_name)) {

                        foreach ($request->file($request_file_name) as $file) {
                            $name = $question . '-' . $answer_question->written_answer_id . Str::uuid() . '.' . $file->extension();
                            $file->move(public_path('images/script/'), $name);
                            $files[] = 'images/script/' . $name;
                        }

                    }

                    foreach ($files as $f) {
                        WrittenAnswerQuestionScript::create([
                            'written_answer_question_id' => $answer_question->id,
                            'student_script' => $f,
                        ]);
                    }

                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Your answer has been submitted',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function storeWrittenAnswer(Request $request)
    {
        DB::beginTransaction();

        try {

            if (WrittenAnswer::where('user_id', $request->user_id)->where('written_id', $request->written_id)->exists()) {
                return $this->errorMessage('This answer has been taken before');
            }

            $answer = WrittenAnswer::create([
                'user_id' => $request->user_id,
                'written_id' => $request->written_id,
                'category' => $request->category,
            ]);

            foreach (json_decode($request->question_id) as $question) {
                $answer_question = WrittenAnswerQuestion::create([
                    'written_answer_id' => $answer->id,
                    'written_question_id' => $question,
                ]);

                $files = [];

                $request_file_name = 'student_script_' . $question;

                if ($request->hasfile($request_file_name)) {

                    foreach ($request->file($request_file_name) as $file) {
                        $name = $question . '-' . $answer_question->written_answer_id . Str::uuid() . '.' . $file->extension();
                        $file->move(public_path('images/script/'), $name);
                        $files[] = 'images/script/' . $name;
                    }

                }

                foreach ($files as $f) {
                    WrittenAnswerQuestionScript::create([
                        'written_answer_question_id' => $answer_question->id,
                        'student_script' => $f,
                    ]);
                }

            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Your answer has been submitted',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
        }

    }

    public function get_top_3_student(Request $request, $written_id)
    {
        $data = WrittenAnswer::where('written_id', $written_id)
            ->with(['user', 'writtenAnswerQuestion.writtenAnswerQuestionScript', 'writtenAnswerQuestion.writtenAnswerQuestion'])
            ->orderBy('obtained_mark', 'desc')->limit(3)->get();

        return $this->successMessage('ok', $data);
    }

}
