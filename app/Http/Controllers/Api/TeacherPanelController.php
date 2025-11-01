<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\TeacherWallet;
use App\Models\TopicSource;
use App\Models\WalletHistory;
use App\Models\Written;
use App\Models\WrittenAnswer;
use App\Models\WrittenAnswerQuestion;
use App\Models\WrittenAnswerQuestionScript;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TeacherPanelController extends Controller {
    public function examAndPaper(Request $request) {
        $search = $request->search;
        $data             = [];
        $data['category'] = $category = $request->category;
        $examId           = WrittenAnswer::where('category', $category)
            ->whereNotNull('teacher_id')
            ->where('teacher_id', Auth::id())
            ->groupBy('written_id')
            ->select('written_id')
            ->pluck('written_id')
            ->toArray();

        $exam = Written::whereIn('id', $examId)
            ->with([
                'answer.user',
                'answer.written' => function ($q) use($search) {
                    if ($search){
                        return $q->where('name', 'LIKE', '%'. $search . '%');
                    }
                },
                'answer' => function ($q) {
                    return $q->where('teacher_id', Auth::id())
                        ->withCount([
                            'writtenAnswerQuestion as checked_question' => function ($aq) {
                                return $aq->where('is_checked_by_teacher', 1);
                            },
                            'writtenAnswerQuestion as total_question',
                        ]);
                },
                'answer.writtenAnswerQuestion.writtenAnswerQuestion',
                'answer.writtenAnswerQuestion.writtenAnswerQuestionScript',
                'answer.review.user',
                'answer.review.conversations',
            ])
            ->withCount([
                'answer as total_examinee' => function ($q) {
                    return $q->where('teacher_id', Auth::id());
                },
                'answer as total_examined' => function ($q) {
                    return $q->where('teacher_id', Auth::id())->where('is_checked', 1);
                },
                'answer as total_reviews' => function ($q) {
                    return $q->where('teacher_id', Auth::id())->whereHas('review');
                },
            ]);

        if ($request->published_date) {
            // return $request->published_date;
            $exam = $exam->whereDate('published_at', $request->published_date);
        }

        $exam = $exam->paginate(200);

        foreach ($exam as $item) {
            $item['subjects'] = Subject::whereIn('id', explode(',', $item->subject_id))->get();
            $item['sources']  = TopicSource::whereIn('id', explode(',', $item->topic_id))->get();
            
            // Calculate teacher review stats for this exam
            $reviewData = [];
            $ratings = [];
            $reviewsDetail = [];
            
            // Get the answer relationship (not the answer column)
            $answers = $item->getRelation('answer');
            if ($answers && is_iterable($answers)) {
                foreach ($answers as $answer) {
                    if ($answer->review) {
                        $ratings[] = $answer->review->rating;
                        
                        // Collect detailed review info including student comment and conversations
                        $reviewsDetail[] = [
                            'review_id' => $answer->review->id,
                            'written_answer_id' => $answer->review->written_answer_id,
                            'student' => $answer->review->user ? [
                                'id' => $answer->review->user->id,
                                'name' => $answer->review->user->name,
                                'email' => $answer->review->user->email,
                            ] : null,
                            'rating' => $answer->review->rating,
                            'comment' => $answer->review->comment,
                            'teacher_reply' => $answer->review->teacher_reply,
                            'replied_at' => $answer->review->replied_at,
                            'created_at' => $answer->review->created_at,
                            'conversations' => $answer->review->conversations ? $answer->review->conversations->map(function($conv) {
                                return [
                                    'id' => $conv->id,
                                    'message' => $conv->message,
                                    'sender_type' => $conv->sender_type,
                                    'sender_id' => $conv->sender_id,
                                    'created_at' => $conv->created_at,
                                ];
                            }) : [],
                        ];
                    }
                }
            }
            
            $reviewData['total_reviews'] = count($ratings);
            $reviewData['average_rating'] = count($ratings) > 0 ? round(array_sum($ratings) / count($ratings), 2) : 0;
            $reviewData['rating_breakdown'] = [
                '5_star' => count(array_filter($ratings, fn($r) => $r == 5)),
                '4_star' => count(array_filter($ratings, fn($r) => $r == 4)),
                '3_star' => count(array_filter($ratings, fn($r) => $r == 3)),
                '2_star' => count(array_filter($ratings, fn($r) => $r == 2)),
                '1_star' => count(array_filter($ratings, fn($r) => $r == 1)),
            ];
            $reviewData['reviews_detail'] = $reviewsDetail;
            
            $item['teacher_review_summary'] = $reviewData;
        }

        if ($request->search) {
            $new_data = [];
            $flag     = false;

            foreach ($exam as $e_item) {
                $flag = false;

                foreach ($e_item->subjects as $sub) {

                    if (str_starts_with($sub->name, $request->search)) {
                        $flag = true;
                        break;
                    }

                }

                foreach ($e_item->sources as $src) {

                    if (str_starts_with($src->topic, $request->search) || str_starts_with($src->source, $request->search)) {
                        $flag = true;
                        break;
                    }

                }

                if ($flag == true) {

                    $new_data[] = $e_item;
                }

            }

            $exam = $new_data;

        }

        $data['exam'] = $exam;

        return $this->successMessage('', $data);
    }

    public function storeExamPaperAssessment(Request $request) {
        DB::beginTransaction();
        try {
            $question = WrittenAnswerQuestion::find($request->written_answer_question_id);

            if ($question) {

                $is_checked_before = $question->writtenAnswer->is_checked == 1 ? true : false;

                if ($request->hasfile('teacher_script')) {

                    foreach ($request->file('teacher_script') as $file) {
                        $name = $question->id . '-' . time() . Str::uuid() . '.' . $file->extension();
                        $file->move(public_path('images/script/'), $name);
                        $files[] = 'images/script/' . $name;
                    }

                }

                $script = WrittenAnswerQuestionScript::where('written_answer_question_id', $question->id)->get();

                foreach ($script as $key => $item) {
                    $image_path = public_path($item->teacher_script);

                    if (File::exists($image_path)) {
                        File::delete($image_path);
                    }

                    $item->update([
                        'teacher_script' => $files[$key],
                    ]);
                }

                $question->update([
                    'is_checked_by_teacher' => 1,
                    'marks'                 => $request->marks,
                    'comment'               => $request->comment,
                ]);

                $written_answer             = WrittenAnswer::find($question->written_answer_id);
                $written_answer->is_checked = $request->is_checked;

                $written_answer->obtained_mark = $request->obtained_mark;
                $written_answer->result_status = $request->obtained_mark >= $written_answer->written->pass_marks ? 1 : 0;
                $written_answer->save();

                if (!$is_checked_before && $written_answer->is_checked == 1) {

                    if (TeacherWallet::where('user_id', Auth::id())->exists()) {
                        $wallet         = TeacherWallet::where('user_id', Auth::id())->first();
                        $wallet->amount = $wallet->amount + auth()->user()->amount;
                        $wallet->save();
                    } else {
                        $wallet          = new TeacherWallet();
                        $wallet->user_id = Auth::id();
                        $wallet->amount  = auth()->user()->amount;
                        $wallet->save();
                    }

                }

                DB::commit();

                return $this->successMessage();

            } else {
                return $this->errorMessage('Something went wrong');
            }

        } catch (Exception $th) {
            DB::rollBack();

            return $this->errorMessage($th->getMessage());
        }

    }

    public function wallet() {
        $data           = [];
        $data['wallet'] = TeacherWallet::where('user_id', Auth::id())->with(['teacherWalletHistory' => function ($q) {
            return $q->latest();
        },
        ])->first();

        return $this->successMessage('', $data);
    }

    public function withdrawalRequest(Request $request) {
        $wallet = TeacherWallet::where('user_id', Auth::id())->first();

        if (!$wallet) {
            return $this->errorMessage('Invalid wallet banalce');
        } elseif ($request->amount < 100) {
            return $this->errorMessage('Withdrawal amount must be grater than or equal to 100');
        } elseif ($wallet->amount < $request->amount) {
            return $this->errorMessage('Insufficient wallet balance');
        }

        WalletHistory::create([
            'teacher_wallet_id' => $wallet->id,
            'amount'            => $request->amount,
            'status'            => 'Pending',
        ]);

        return $this->successMessage();

    }

    public function dashboard() {
        $data   = [];
        $script = DB::table('written_answers')
            ->where('teacher_id', Auth::id())
            ->groupby('written_id')
            ->selectRaw('written_id')
            ->selectRaw("count(id) as total_script")
            ->selectRaw("count(case when is_checked = 1 then 1 end) as checked_script")
            ->selectRaw("count(case when is_checked = 0 then 1 end) as left_script")
            ->get();
        $current_script = [];

        foreach ($script as $item) {

            if ($item->left_script > 0) {
                $item->written    = Written::find($item->written_id);
                $current_script[] = $item;
            }

        }

        $data['current_script']        = $current_script;
        $data['total_examined_script'] = WrittenAnswer::where('teacher_id', Auth::id())->where('is_checked', 1)->count();

        $data['wallet'] = TeacherWallet::where('user_id', Auth::id())->first();

        return $this->successMessage('', $data);
    }

}
