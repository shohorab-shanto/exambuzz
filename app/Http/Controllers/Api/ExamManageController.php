<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Favorite;
use App\Models\Helper;
use App\Models\PreliminaryAnswer;
use App\Models\Subject;
use App\Models\Syllabus;
use App\Models\TopicSource;
use App\Models\Written;
use App\Models\WrittenAnswer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ExamManageController extends Controller
{
    public function checkLiveExam(Request $request)
    {
        $data = [];

        $data['category'] = $category = $request->category;
        $data['subcategory'] = $sub = $request->subcategory;
        $data['childcategory'] = $child = $request->childcategory;
        $is_live = false;

        $exams = [];
        $upcoming_exam_date = null;
        // dd($sub);
        if ($sub === 'Preliminary') {
            $exams = Exam::where('status', 1)->where('published_at', '<=', Carbon::now('Asia/Dhaka')->toDateTimeString())
                ->where('expired_at', '>=', Carbon::now('Asia/Dhaka')->toDateTimeString())
                ->where('category', $category)
                ->where('subcategory', $sub);

            if ($child) {
                $exams = $exams->where('childcategory', $child);
            }

            $exams = $exams->with([
                'questions.questionOptions',
                'questions.subject',
                'questions.topic',
                'userAnswer' => function ($q) {
                    return $q->where('user_id', Auth::id());
                },
            ])->get();

            // Get upcoming exam date
            $upcomingExamQuery = Exam::where('status', 1)
                ->where('published_at', '>', Carbon::now('Asia/Dhaka')->toDateTimeString())
                ->where('category', $category)
                ->where('subcategory', $sub);

            if ($child) {
                $upcomingExamQuery = $upcomingExamQuery->where('childcategory', $child);
            }

            $upcomingExam = $upcomingExamQuery->orderBy('published_at', 'asc')->first();
            if ($upcomingExam) {
                $upcoming_exam_date = $upcomingExam->published_at;
            }

        } elseif ($sub === 'Written') {
            $exams = Written::where('status', 1)->where('published_at', '<=', Carbon::now('Asia/Dhaka')->toDateTimeString())
                ->where('expired_at', '>=', Carbon::now('Asia/Dhaka')->toDateTimeString())
                ->where('category', $category)
                ->where('subcategory', $sub);

            if ($child) {
                $exams = $exams->where('childcategory', $child);
            }

            $exams = $exams->with([
                'writtenQuestion',
                'userAnswer' => function ($q) {
                    return $q->where('user_id', Auth::id());
                },
            ])->get();

            // Get upcoming exam date
            $upcomingExamQuery = Written::where('status', 1)
                ->where('published_at', '>', Carbon::now('Asia/Dhaka')->toDateTimeString())
                ->where('category', $category)
                ->where('subcategory', $sub);

            if ($child) {
                $upcomingExamQuery = $upcomingExamQuery->where('childcategory', $child);
            }

            $upcomingExam = $upcomingExamQuery->orderBy('published_at', 'asc')->first();
            if ($upcomingExam) {
                $upcoming_exam_date = $upcomingExam->published_at;
            }

        }

        $data['exams'] = $exams ?? [];

        if ($exams && is_countable($exams) && count($exams) > 0) {
            // Attach subjects and sources to each exam
            foreach ($exams as $exam) {
                if ($exam->subject_id && $exam->topic_id) {
                    $exam['subjects'] = Subject::whereIn('id', explode(',', $exam->subject_id))->get();
                    $exam['sources'] = TopicSource::whereIn('id', explode(',', $exam->topic_id))->get();
                }
            }

            $is_live = true;
        }

        $data['is_live_exam'] = $is_live;
        $data['total_live_exams'] = is_countable($exams) ? count($exams) : 0;
        $data['upcoming_exam_date'] = $upcoming_exam_date;

        return $this->successMessage('', $data);
    }

    public function routine(Request $request)
    {
        $data = [];

        $data['category'] = $category = $request->category;
        $data['subcategory'] = $sub = $request->subcategory;
        $data['childcategory'] = $child = $request->childcategory;

        if ($sub === 'Preliminary') {
            $exam = Exam::where('status', 1)->where('published_at', '>', Carbon::now('Asia/Dhaka')->toDateTimeString())
                ->where('category', $category)
                ->where('subcategory', $sub);

            if ($child) {
                $exam = $exam->where('childcategory', $child);
            }

            $exam = $exam->orderBy('published_at', 'asc')->paginate();

            foreach ($exam as $item) {
                $item['subjects'] = Subject::whereIn('id', explode(',', $item->subject_id))->get();
                $item['sources'] = TopicSource::whereIn('id', explode(',', $item->topic_id))->get();
            }

        } elseif ($sub === 'Written') {
            $exam = Written::where('status', 1)->where('published_at', '>', Carbon::now('Asia/Dhaka')->toDateTimeString())
                ->where('category', $category)
                ->where('subcategory', $sub);

            if ($child) {
                $exam = $exam->where('childcategory', $child);
            }

            $exam = $exam->orderBy('published_at', 'asc')->paginate();

            foreach ($exam as $item) {
                $item['subjects'] = Subject::whereIn('id', explode(',', $item->subject_id))->get();
                $item['sources'] = TopicSource::whereIn('id', explode(',', $item->topic_id))->get();
            }

        }

        $data['exam'] = $exam;

        return $this->successMessage('', $data);

    }

    public function allRoutine(Request $request)
    {
        $data = [];

        $data['category'] = $category = $request->category;
        $data['subcategory'] = $sub = $request->subcategory;
        $data['childcategory'] = $child = $request->childcategory;

        if ($sub === 'Preliminary') {
            $exam = Exam::where('status', 1)->where('published_at', '>', Carbon::now('Asia/Dhaka')->toDateTimeString());

            $exam = $exam->orderBy('published_at', 'asc')->paginate();

            foreach ($exam as $item) {
                $item['subjects'] = Subject::whereIn('id', explode(',', $item->subject_id))->get();
                $item['sources'] = TopicSource::whereIn('id', explode(',', $item->topic_id))->get();
            }

        } elseif ($sub === 'Written') {
            $exam = Written::where('status', 1)->where('published_at', '>', Carbon::now('Asia/Dhaka')->toDateTimeString());

            $exam = $exam->orderBy('published_at', 'asc')->paginate();

            foreach ($exam as $item) {
                $item['subjects'] = Subject::whereIn('id', explode(',', $item->subject_id))->get();
                $item['sources'] = TopicSource::whereIn('id', explode(',', $item->topic_id))->get();
            }

        }

        $data['exam'] = $exam;

        return $this->successMessage('', $data);

    }

    public function archive(Request $request)
    {
        $data = [];

        $data['category'] = $category = $request->category;
        $data['subcategory'] = $sub = $request->subcategory;
        $data['childcategory'] = $child = $request->childcategory;
        $search = $request->search;
        if ($sub === 'Preliminary') {
            // Initial query to fetch exams based on status, expiration, and subcategory
            $examQuery = Exam::where('status', 1)
                ->where('expired_at', '<', Carbon::now('Asia/Dhaka')->toDateTimeString())
                ->where('subcategory', $sub);

            // Apply additional filters if they are provided
            if ($category) {
                $examQuery = $examQuery->where('category', $category);
            }

            if ($child) {
                $examQuery = $examQuery->where('childcategory', $child);
            }

            if ($request->subject_id) {
                $examQuery = $examQuery->where('subject_id', 'LIKE', $request->subject_id . '%');
            }

            if ($request->package_id) {
                $examQuery = $examQuery->where('package_id', $request->package_id);
            }

            // Topic-based filter (by TopicSource topic/source text)
            if ($request->topic) {
                $topicIds = TopicSource::where('topic', 'LIKE', '%' . $request->topic . '%')
                    ->orWhere('source', 'LIKE', '%' . $request->topic . '%')
                    ->pluck('id');

                if ($topicIds->count() > 0) {
                    $examQuery = $examQuery->where(function ($q) use ($topicIds) {
                        foreach ($topicIds as $tid) {
                            $q->orWhereRaw('FIND_IN_SET(?, topic_id)', [$tid]);
                        }
                    });
                } else {
                    // No matching topics, return empty set
                    $examQuery = $examQuery->whereRaw('1 = 0');
                }
            }

            if ($search) {
                $examQuery = $examQuery->where('name', 'LIKE', '%' . $search . '%');
            }

            // Fetch exams with additional data and pagination
            $exams = $examQuery->orderByDesc('id')
                ->with([
                    'userAnswer' => function ($q) {
                        return $q->where('user_id', Auth::id());
                    },
                ])
                ->withCount('questions')
                ->paginate();

            // Attach related subjects and sources to each exam
            foreach ($exams as $exam) {
                $exam['subjects'] = Subject::whereIn('id', explode(',', $exam->subject_id))->get();
                $exam['sources'] = TopicSource::whereIn('id', explode(',', $exam->topic_id))->get();
            }


            $data = ['exam' => $exams];

            return $this->successMessage('', $data);

        } elseif ($sub === 'Written') {
            $exam = Written::where('status', 1)->where('expired_at', '<', Carbon::now('Asia/Dhaka')->toDateTimeString())
                ->where('subcategory', $sub);

            if ($category) {
                $exam = $exam->where('category', $category);
            }

            if ($child) {
                $exam = $exam->where('childcategory', $child);
            }

            if ($request->search) {
                $exam = $exam->where('name', 'LIKE', '%'. $request->search . '%');
            }

            if ($request->subject_id) {
                $exam = $exam->where('subject_id', 'LIKE', $request->subject_id . '%');
            }

            if ($request->package_id) {
                $exam = $exam->where('package_id', $request->package_id);
            }

            // Topic-based filter (by TopicSource topic/source text)
            if ($request->topic) {
                $topicIds = TopicSource::where('topic', 'LIKE', '%' . $request->topic . '%')
                    ->orWhere('source', 'LIKE', '%' . $request->topic . '%')
                    ->pluck('id');

                if ($topicIds->count() > 0) {
                    $exam = $exam->where(function ($q) use ($topicIds) {
                        foreach ($topicIds as $tid) {
                            $q->orWhereRaw('FIND_IN_SET(?, topic_id)', [$tid]);
                        }
                    });
                } else {
                    // No matching topics, return empty set
                    $exam = $exam->whereRaw('1 = 0');
                }
            }

            $exam = $exam->orderByDesc('id')
                ->with([
                    'writtenQuestion',
                    'userAnswer' => function ($q) {
                        return $q->where('user_id', Auth::id());
                    },
                ])
                ->withCount('writtenQuestion')
                ->paginate();

            if ($request->search) {
                $exam = Written::where('status', 1)->whereDate('expired_at', '<', date('Y-m-d'))
                    ->where('subcategory', $sub);

                if ($category) {
                    $exam = $exam->where('category', $category);
                }

                if ($request->search) {
                    $exam = $exam->where('name', 'LIKE', '%'. $request->search . '%');
                }

                if ($child) {
                    $exam = $exam->where('childcategory', $child);
                }

                if ($request->package_id) {
                    $exam = $exam->where('package_id', $request->package_id);
                }

                // Topic-based filter (by TopicSource topic/source text) in search branch
                if ($request->topic) {
                    $topicIds = TopicSource::where('topic', 'LIKE', '%' . $request->topic . '%')
                        ->orWhere('source', 'LIKE', '%' . $request->topic . '%')
                        ->pluck('id');

                    if ($topicIds->count() > 0) {
                        $exam = $exam->where(function ($q) use ($topicIds) {
                            foreach ($topicIds as $tid) {
                                $q->orWhereRaw('FIND_IN_SET(?, topic_id)', [$tid]);
                            }
                        });
                    } else {
                        $exam = $exam->whereRaw('1 = 0');
                    }
                }

                $exam = $exam->orderByDesc('id')
                    ->with([
                        'userAnswer' => function ($q) {
                            return $q->where('user_id', Auth::id());
                        },
                    ])
                    ->withCount('writtenQuestion')
                    ->get();
            }

            foreach ($exam as $item) {
                $item['subjects'] = Subject::whereIn('id', explode(',', $item->subject_id))->get();
                $item['sources'] = TopicSource::whereIn('id', explode(',', $item->topic_id))->get();
            }

            if ($request->search) {
                $new_data = [];
                $flag = false;

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

                $data['exam'] = $new_data;

                return $this->successMessage('', $data);
            }

            $data['exam'] = $exam;

            return $this->successMessage('', $data);
        }

        return $this->successMessage('', $data);

    }

    public function syllabus(Request $request)
    {
        $data = [];

        $data['category'] = $category = $request->category;
        $data['subcategory'] = $sub = $request->subcategory;
        $data['childcategory'] = $child = $request->childcategory;
        $data['syllabus'] = Syllabus::where('category', $category)
            ->where('subcategory', $sub)
            ->where('childcategory', $child)
            ->first();

        return $this->successMessage('', $data);
    }

    public function archiveExamQuestionDetails(Request $request)
    {
        $data = [];

        $data['category'] = $category = $request->category;
        $data['subcategory'] = $sub = $request->subcategory;
        $data['childcategory'] = $child = $request->childcategory;

        if ($sub === 'Preliminary') {
            $exam = Exam::where('status', 1)->where('id', $request->exam_id)
                ->with('questions.questionOptions', 'questions.subject', 'questions.topic')
                ->first();
            $subjects = Subject::whereIn('id', explode(',', $exam->subject_id))->get();
            $sources = TopicSource::whereIn('id', explode(',', $exam->topic_id))->get();
        } else

            if ($sub === 'Written') {
                $exam = Written::where('status', 1)->where('id', $request->exam_id)->with('writtenQuestion')->first();

                $subjects = Subject::whereIn('id', explode(',', $exam->subject_id))->get();
                $sources = TopicSource::whereIn('id', explode(',', $exam->topic_id))->get();
            }

        $data['exam'] = $exam;
        $data['subjects'] = $subjects;
        $data['sources'] = $sources;

        return $this->successMessage('', $data);
    }

    public function toggleFavorite(Request $request)
    {
        $data = [];

        $data['category'] = $category = $request->category;
        $data['subcategory'] = $sub = $request->subcategory;
        $data['childcategory'] = $child = $request->childcategory;

        $favorite = Favorite::where('user_id', Auth::id())
            ->where('question_id', $request->question_id)
            ->where('category', $category)
            ->where('subcategory', $sub)
            ->where('childcategory', $child)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return $this->successMessage('Question removed from favorite');
        }

        Favorite::create([
            'category' => $category,
            'subcategory' => $sub,
            'childcategory' => $child,
            'question_id' => $request->question_id,
            'subject_id' => $request->subject_id,
            'user_id' => Auth::id(),
        ]);

        return $this->successMessage('Question added to favourite');
    }

    public function favoriteList(Request $request)
    {
        $data = [];

        $data['category'] = $category = $request->category;
        $data['subcategory'] = $sub = $request->subcategory;
        $data['childcategory'] = $child = $request->childcategory;

        if ($sub === 'Preliminary') {

            $favorite = Favorite::where('user_id', Auth::id())
                ->where('category', $category)
                ->where('subcategory', $sub)
                ->where('childcategory', $child);

            if ($request->subject_id) {
                $favorite = $favorite->where('subject_id', $request->subject_id);
            }

            if ($request->search) {
                $favorite = $favorite->whereHas('preliQuestion', function ($q) use ($request) {
                    return $q->where('question_name', 'LIKE', '%' . $request->search . '%');
                });
            }

            $favorite = $favorite->with('preliQuestion.questionOptions', 'preliQuestion.subject', 'preliQuestion.topic')
                ->latest()
                ->paginate();
        } else {
            $favorite = Favorite::where('user_id', Auth::id())
                ->where('category', $category)
                ->where('subcategory', $sub)
                ->where('childcategory', $child);

            if ($request->subject_id) {
                $favorite = $favorite->where('subject_id', $request->subject_id);
            }

            if ($request->search) {
                $favorite = $favorite->whereHas('writtenQuestion', function ($q) use ($request) {
                    return $q->where('name', 'LIKE', '%' . $request->search . '%');
                });
            }

            $favorite = $favorite->with('writtenQuestion')
                ->latest()
                ->paginate();
        }

        $data['favorite'] = $favorite;

        return $this->successMessage('', $data);

    }

    public function resultList(Request $request)
    {
        $data = [];

        $data['category'] = $category = $request->category;
        $data['subcategory'] = $sub = $request->subcategory;
        $data['childcategory'] = $child = $request->childcategory;
        $data['search'] = $search = $request->search;

        if ($sub === 'Preliminary') {
            $answer = PreliminaryAnswer::where('user_id', Auth::id())
                ->whereHas('exam', function ($q) use ($category) {
                    return $q->where('category', $category);
                })
                ->whereHas('exam', function ($q) use ($sub) {
                    return $q->where('subcategory', $sub);
                });

            if ($child) {
                $answer = $answer->whereHas('exam', function ($q) use ($child) {
                    return $q->where('childcategory', $child);
                });
            }

            if ($request->subject_id) {
                $answer = $answer->whereHas('exam', function ($q) use ($request) {
                    return $q->where('subject_id', 'LIKE', $request->subject_id . '%');
                });
            }

            // Topic-based filter by TopicSource text against exam.topic_id CSV
            if ($request->topic) {
                $topicIds = TopicSource::where('topic', 'LIKE', '%' . $request->topic . '%')
                    ->orWhere('source', 'LIKE', '%' . $request->topic . '%')
                    ->pluck('id');

                if ($topicIds->count() > 0) {
                    $answer = $answer->whereHas('exam', function ($q) use ($topicIds) {
                        $q->where(function ($qq) use ($topicIds) {
                            foreach ($topicIds as $tid) {
                                $qq->orWhereRaw('FIND_IN_SET(?, topic_id)', [$tid]);
                            }
                        });
                    });
                } else {
                    // Force empty result when no matching topic IDs
                    $answer = $answer->whereRaw('1 = 0');
                }
            }

            $answer = $answer->latest()->paginate();

            foreach ($answer as $item) {
                $item['subjects'] = Subject::whereIn('id', explode(',', $item->exam->subject_id))->get();
                $item['sources'] = TopicSource::whereIn('id', explode(',', $item->exam->topic_id))->get();
            }

            if ($request->search) {
                $needle = $request->search;
                $new_data = [];

                foreach ($answer as $e_item) {
                    $matched = false;

                    // match by exam name
                    if (isset($e_item->exam) && $e_item->exam && stripos($e_item->exam->name ?? '', $needle) !== false) {
                        $matched = true;
                    }

                    // match by subject name
                    if (!$matched) {
                        foreach ($e_item->subjects as $subj) {
                            if ($subj->name !== null && stripos($subj->name, $needle) !== false) {
                                $matched = true;
                                break;
                            }
                        }
                    }

                    // match by topic/source
                    if (!$matched) {
                        foreach ($e_item->sources as $src) {
                            $topicMatches = $src->topic !== null && stripos($src->topic, $needle) !== false;
                            $sourceMatches = $src->source !== null && stripos($src->source, $needle) !== false;
                            if ($topicMatches || $sourceMatches) {
                                $matched = true;
                                break;
                            }
                        }
                    }

                    if ($matched) {
                        $new_data[] = $e_item;
                    }
                }

                $answer = $new_data;
            }

        } else {
            $answer = WrittenAnswer::where('user_id', Auth::id())
                ->where('is_checked', 1)
                ->whereHas('written', function ($q) use ($category) {
                    return $q->where('category', $category);
                })
                
                ->whereHas('written', function ($q) use ($sub) {
                    return $q->where('subcategory', $sub);
                });

            if ($child) {
                $answer = $answer->whereHas('written', function ($q) use ($child) {
                    return $q->where('childcategory', $child);
                });
            }

            if ($request->subject_id) {
                $answer = $answer->whereHas('written', function ($q) use ($request) {
                    return $q->where('subject_id', 'LIKE', $request->subject_id . '%');
                });
            }

            // Topic-based filter by TopicSource text against written.topic_id CSV
            if ($request->topic) {
                $topicIds = TopicSource::where('topic', 'LIKE', '%' . $request->topic . '%')
                    ->orWhere('source', 'LIKE', '%' . $request->topic . '%')
                    ->pluck('id');

                if ($topicIds->count() > 0) {
                    $answer = $answer->whereHas('written', function ($q) use ($topicIds) {
                        $q->where(function ($qq) use ($topicIds) {
                            foreach ($topicIds as $tid) {
                                $qq->orWhereRaw('FIND_IN_SET(?, topic_id)', [$tid]);
                            }
                        });
                    });
                } else {
                    $answer = $answer->whereRaw('1 = 0');
                }
            }

            $answer = $answer->latest()->paginate();

            foreach ($answer as $item) {
                $item['subjects'] = Subject::whereIn('id', explode(',', $item->written->subject_id))->get();
                $item['sources'] = TopicSource::whereIn('id', explode(',', $item->written->topic_id))->get();
            }

            if ($request->search) {
                $needle = $request->search;
                $new_data = [];

                foreach ($answer as $e_item) {
                    $matched = false;

                    // match by written name
                    if (isset($e_item->written) && $e_item->written && stripos($e_item->written->name ?? '', $needle) !== false) {
                        $matched = true;
                    }

                    // match by subject name
                    if (!$matched) {
                        foreach ($e_item->subjects as $subj) {
                            if ($subj->name !== null && stripos($subj->name, $needle) !== false) {
                                $matched = true;
                                break;
                            }
                        }
                    }

                    // match by topic/source
                    if (!$matched) {
                        foreach ($e_item->sources as $src) {
                            $topicMatches = $src->topic !== null && stripos($src->topic, $needle) !== false;
                            $sourceMatches = $src->source !== null && stripos($src->source, $needle) !== false;
                            if ($topicMatches || $sourceMatches) {
                                $matched = true;
                                break;
                            }
                        }
                    }

                    if ($matched) {
                        $new_data[] = $e_item;
                    }
                }

                $answer = $new_data;
            }

        }

        return $this->successMessage('ok', $answer);
    }

    public function subjectList()
    {
        $data = Subject::with('topicAndSources')->get();

        return $this->successMessage('', $data);
    }

    public function meritListv2(Request $request)
    {
        $data = [];

        $data['category'] = $category = $request->category;
        $data['subcategory'] = $sub = $request->subcategory;
        $data['childcategory'] = $child = $request->childcategory;
        $examName = 'প্রিলিমিনারি';


        if ($sub === 'Preliminary') {
            $exam = PreliminaryAnswer::where('user_id', Auth::id())
                ->latest()
                ->whereHas('exam', function ($q) {
                    return $q->where('published_at', '<=', Carbon::now('Asia/Dhaka')->toDateTimeString())
                        ->where('expired_at', '<', Carbon::now('Asia/Dhaka')->toDateTimeString());
                })
                ->whereHas('exam', function ($q) use ($category) {
                    return $q->where('category', $category);
                })
                ->whereHas('exam', function ($q) use ($sub) {
                    return $q->where('subcategory', $sub);
                });

            if ($child) {
                $exam = $exam->whereHas('exam', function ($q) use ($child) {
                    return $q->where('childcategory', $child);
                });
            }

            $exam = $exam->first();

            if (!isset($exam)) {
                return $this->errorMessage('আপনি কোন ' . $examName . ' পরীক্ষায় অংশগ্রহণ করেননি।');
            }

            $get_exam_answer = [];

            // return $this->successMessage('ok', $exam);

            $find_exam = Exam::where('id', $exam->exam_id)->select('id', 'pass_marks', 'expired_at')->first();


            if ($exam) {
                $get_exam_answer = PreliminaryAnswer::where('exam_id', $exam->exam_id)
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
            }

            $perPage = 15;
            $page = request()->get('page', 1);
            $get_exam_answer = Helper::CustomPaginate($perPage, $page, $get_exam_answer, $request->search);

            $examName = 'প্রিলিমিনারি';
        } else {
            $written = WrittenAnswer::where('user_id', Auth::id())
                ->where('is_checked', 1)
                ->latest()
                ->whereHas('written', function ($q) {
                    return $q->where('published_at', '<=', Carbon::now('Asia/Dhaka')->toDateTimeString())
                        ->where('expired_at', '<', Carbon::now('Asia/Dhaka')->toDateTimeString());
                })
                ->whereHas('written', function ($q) use ($category) {
                    return $q->where('category', $category);
                })
                ->whereHas('written', function ($q) use ($sub) {
                    return $q->where('subcategory', $sub);
                });

            if ($child) {
                $written = $written->whereHas('written', function ($q) use ($child) {
                    return $q->where('childcategory', $child);
                });
            }

            $written = $written->first();

            $get_exam_answer = [];

            if ($written) {
                $get_exam_answer = WrittenAnswer::where('written_id', $written->written_id)
                    ->where('is_checked', 1)
                    ->leftJoin('users', function ($join) {
                        $join->on('written_answers.user_id', '=', 'users.id');
                    })
                    ->select(
                        'written_answers.id',
                        'written_answers.user_id',
                        'written_answers.obtained_mark',
                        'users.name as user_name'
                    )
                    ->orderBy('written_answers.obtained_mark', 'desc')->get();

                $get_exam_answer = Helper::SetPosition($get_exam_answer);

            }

            $perPage = 15;
            $page = request()->get('page', 1);
            $get_exam_answer = Helper::CustomPaginate($perPage, $page, $get_exam_answer, $request->search);

            $examName = 'লিখিত';
        }

        if (count($get_exam_answer) > 0) {
            return $this->successMessage('ok', $get_exam_answer);
        } else {
            return $this->errorMessage('আপনি কোন ' . $examName . ' পরীক্ষায় অংশগ্রহণ করেননি।');
        }
    }

    public function meritList(Request $request)
    {

        $data = [];

        $data['category'] = $category = $request->category;
        $data['subcategory'] = $sub = $request->subcategory;
        $data['childcategory'] = $child = $request->childcategory;
        $examName = 'প্রিলিমিনারি';


        if ($sub === 'Preliminary') {
            $exam = PreliminaryAnswer::where('user_id', Auth::id())
                ->latest()
                ->whereHas('exam', function ($q) {
                    return $q->where('published_at', '<=', Carbon::now('Asia/Dhaka')->toDateTimeString())
                        ->where('expired_at', '<', Carbon::now('Asia/Dhaka')->toDateTimeString());
                })
                ->whereHas('exam', function ($q) use ($category) {
                    return $q->where('category', $category);
                })
                ->whereHas('exam', function ($q) use ($sub) {
                    return $q->where('subcategory', $sub);
                });

            if ($child) {
                $exam = $exam->whereHas('exam', function ($q) use ($child) {
                    return $q->where('childcategory', $child);
                });
            }

            $exam = $exam->first();

            $get_exam_answer = [];

            // return $this->successMessage('ok', $exam);

            $find_exam = Exam::where('id', $exam->exam_id)->select('id', 'pass_marks', 'expired_at')->first();


            if ($exam) {
                $get_exam_answer = PreliminaryAnswer::where('exam_id', $exam->exam_id)
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
            }

            $perPage = 15;
            $page = request()->get('page', 1);
            $get_exam_answer = Helper::CustomPaginate($perPage, $page, $get_exam_answer);

            $examName = 'প্রিলিমিনারি';
        } else {
            $written = WrittenAnswer::where('user_id', Auth::id())
                ->where('is_checked', 1)
                ->latest()
                ->whereHas('written', function ($q) {
                    return $q->where('published_at', '<=', Carbon::now('Asia/Dhaka')->toDateTimeString())
                        ->where('expired_at', '<', Carbon::now('Asia/Dhaka')->toDateTimeString());
                })
                ->whereHas('written', function ($q) use ($category) {
                    return $q->where('category', $category);
                })
                ->whereHas('written', function ($q) use ($sub) {
                    return $q->where('subcategory', $sub);
                });

            if ($child) {
                $written = $written->whereHas('written', function ($q) use ($child) {
                    return $q->where('childcategory', $child);
                });
            }

            $written = $written->first();

            $get_exam_answer = [];

            if ($written) {
                $get_exam_answer = WrittenAnswer::where('written_id', $written->written_id)
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

                $get_exam_answer_position = WrittenAnswer::where('written_id', $written->written_id)
                    ->where('is_checked', 1)
                    ->select(['id', 'user_id', 'obtained_mark'])
                    ->orderBy('obtained_mark', 'desc')
                    ->pluck('user_id')
                    ->toArray();

                foreach ($get_exam_answer as $key => $item) {

                    $item['position'] = array_search($item->user_id, $get_exam_answer_position) + 1;
                }

            }

            $examName = 'লিখিত';
        }

        if (count($get_exam_answer) > 0) {
            return $this->successMessage('ok', $get_exam_answer);
        } else {
            return $this->errorMessage('আপনি কোন ' . $examName . ' পরীক্ষায় অংশগ্রহণ করেননি।');
        }

    }

}
