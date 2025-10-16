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
                return $this->errorMessage('You have not participated in this exam yet.', '');
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

            // Calculate subject and topic based breakdown
            $subjectBreakdown = $this->calculateSubjectTopicBreakdown($answer);
            $data['subject_breakdown'] = $subjectBreakdown['subject_breakdown'];
            $data['topic_breakdown'] = $subjectBreakdown['topic_breakdown'];

            $data['answer'] = $answer;

        } elseif ($request->written_id) {

            if (!WrittenAnswer::where('user_id', Auth::id())->where('written_id', $request->written_id)->exists()) {
                return $this->errorMessage('You have not participated in this exam yet.', '');
            }

            $answer = WrittenAnswer::where('user_id', Auth::id())
                ->where('written_id', $request->written_id)
                ->with(
                    'written',
                    'user',
                    'teacher',
                    'writtenAnswerQuestion.writtenAnswerQuestion',
                    'writtenAnswerQuestion.writtenAnswerQuestionScript',
                    'review.user',
                    'review.teacher',
                    'review.conversations.user' // Include conversation thread
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

    /**
     * Calculate subject and topic based breakdown of answers
     */
    private function calculateSubjectTopicBreakdown($answer)
    {
        // Parse user's answer
        $root_answer = str_replace("A", 0, $answer->answer);
        $root_answer = str_replace("B", 1, $root_answer);
        $root_answer = str_replace("C", 2, $root_answer);
        $root_answer = json_decode(str_replace("D", 3, $root_answer));

        // Get all questions with their subjects and topics
        $questions = ExamQuestion::where('exam_id', $answer->exam_id)
            ->with(['questionOptions', 'subject', 'topic'])
            ->get();

        $subjectStats = [];
        $topicStats = [];

        foreach ($questions as $key => $question) {
            $subject_id = $question->subject_id;
            $topic_id = $question->topic_id;
            
            // Initialize subject stats if not exists
            if (!isset($subjectStats[$subject_id])) {
                $subjectStats[$subject_id] = [
                    'subject_id' => $subject_id,
                    'subject_name' => $question->subject ? $question->subject->name : 'Unknown',
                    'total_questions' => 0,
                    'correct' => 0,
                    'wrong' => 0,
                    'skipped' => 0,
                ];
            }

            // Initialize topic stats if not exists
            if ($topic_id && !isset($topicStats[$topic_id])) {
                $topicStats[$topic_id] = [
                    'topic_id' => $topic_id,
                    'topic_name' => $question->topic ? $question->topic->topic : 'Unknown',
                    'subject_id' => $subject_id,
                    'subject_name' => $question->subject ? $question->subject->name : 'Unknown',
                    'total_questions' => 0,
                    'correct' => 0,
                    'wrong' => 0,
                    'skipped' => 0,
                ];
            }

            $subjectStats[$subject_id]['total_questions']++;
            if ($topic_id) {
                $topicStats[$topic_id]['total_questions']++;
            }

            // Check if answer is correct, wrong, or skipped
            $userAnswer = isset($root_answer->$key) ? $root_answer->$key : '';
            
            if ($question->questionOptions->count() > 0) {
                $correctAnswerIndex = null;
                foreach ($question->questionOptions as $option_key => $option) {
                    if ($option->is_answer == 1) {
                        $correctAnswerIndex = $option_key;
                        break;
                    }
                }

                if ($userAnswer === '') {
                    // Skipped
                    $subjectStats[$subject_id]['skipped']++;
                    if ($topic_id) {
                        $topicStats[$topic_id]['skipped']++;
                    }
                } elseif ($correctAnswerIndex !== null && $userAnswer == $correctAnswerIndex) {
                    // Correct
                    $subjectStats[$subject_id]['correct']++;
                    if ($topic_id) {
                        $topicStats[$topic_id]['correct']++;
                    }
                } else {
                    // Wrong
                    $subjectStats[$subject_id]['wrong']++;
                    if ($topic_id) {
                        $topicStats[$topic_id]['wrong']++;
                    }
                }
            } else {
                // No options, consider as skipped
                $subjectStats[$subject_id]['skipped']++;
                if ($topic_id) {
                    $topicStats[$topic_id]['skipped']++;
                }
            }
        }

        return [
            'subject_breakdown' => array_values($subjectStats),
            'topic_breakdown' => array_values($topicStats),
        ];
    }

    /**
     * Student submits review (rating + comment) for evaluated answer sheet
     * Similar to Google Play Store review system
     */
    public function submitReview(Request $request)
    {
        $request->validate([
            'written_answer_id' => 'required|exists:written_answers,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        // Get the written answer
        $writtenAnswer = WrittenAnswer::where('id', $request->written_answer_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$writtenAnswer) {
            return $this->errorMessage('Answer sheet not found or you do not have permission.');
        }

        // Check if answer is evaluated
        if ($writtenAnswer->is_checked == 0) {
            return $this->errorMessage('Cannot submit review before teacher evaluation is complete.');
        }

        // Check if teacher_id exists
        if (!$writtenAnswer->teacher_id) {
            return $this->errorMessage('No teacher assigned to this evaluation.');
        }

        // Check if review already exists
        $existingReview = \App\Models\WrittenAnswerReview::where('written_answer_id', $request->written_answer_id)->first();

        if ($existingReview) {
            // Update existing review
            $existingReview->update([
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            return $this->successMessage('Review updated successfully', $existingReview);
        }

        // Create new review
        $review = \App\Models\WrittenAnswerReview::create([
            'written_answer_id' => $request->written_answer_id,
            'user_id' => Auth::id(),
            'teacher_id' => $writtenAnswer->teacher_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return $this->successMessage('Review submitted successfully', $review);
    }

    /**
     * Add message to review conversation (Student, Teacher, or Admin)
     * Supports ongoing conversation thread
     */
    public function addConversationMessage(Request $request)
    {
        $request->validate([
            'review_id' => 'required|exists:written_answer_reviews,id',
            'message' => 'required|string|max:2000',
        ]);

        // Get the review
        $review = \App\Models\WrittenAnswerReview::where('id', $request->review_id)->first();

        if (!$review) {
            return $this->errorMessage('Review not found.');
        }

        $user = Auth::user();
        
        // Determine user type
        $userType = 'student';
        if ($user->id == $review->teacher_id) {
            $userType = 'teacher';
        }
        // Check if user is admin (you can customize this check based on your admin detection)
        if (isset($user->is_admin) && $user->is_admin == 1) {
            $userType = 'admin';
        }

        // Verify user has permission to comment
        // Students can only comment on their own reviews
        // Teachers/Admins can reply to reviews they're associated with or any review (for admins)
        if ($userType === 'student' && $review->user_id != $user->id) {
            return $this->errorMessage('You can only comment on your own reviews.');
        }

        if ($userType === 'teacher' && $review->teacher_id != $user->id) {
            return $this->errorMessage('You can only reply to your own reviews.');
        }

        // Create conversation message
        $conversation = \App\Models\WrittenAnswerReviewConversation::create([
            'review_id' => $request->review_id,
            'user_id' => $user->id,
            'user_type' => $userType,
            'message' => $request->message,
        ]);

        // Load user relationship
        $conversation->load('user');

        return $this->successMessage('Message posted successfully', $conversation);
    }

    /**
     * Get all conversation messages for a review
     */
    public function getReviewConversation(Request $request)
    {
        $request->validate([
            'review_id' => 'required|exists:written_answer_reviews,id',
        ]);

        $review = \App\Models\WrittenAnswerReview::with([
            'conversations.user',
            'user',
            'teacher',
            'writtenAnswer'
        ])->find($request->review_id);

        if (!$review) {
            return $this->errorMessage('Review not found.');
        }

        return $this->successMessage('Conversation retrieved successfully', $review);
    }

    /**
     * Legacy method - kept for backward compatibility
     * Now creates a conversation message instead
     */
    public function replyToReview(Request $request)
    {
        $request->validate([
            'review_id' => 'required|exists:written_answer_reviews,id',
            'reply' => 'required|string|max:1000',
        ]);

        // Redirect to new conversation method
        $request->merge(['message' => $request->reply]);
        return $this->addConversationMessage($request);
    }

}
