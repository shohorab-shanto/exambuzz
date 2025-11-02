<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RevisionFavorite;
use App\Models\RevisionRead;
use App\Models\RevisionSubject;
use App\Models\RevisionTopicQuestion;
use App\Models\RevisionTopicSource;
use App\Models\Subject;
use App\Models\TopicSource;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionRead;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RevisionController extends Controller
{
    protected $perPage = 15;

    // getRevisionSubjectList
    public function getRevisionSubjectList()
    {
        // with pagination
        $perPage = Subject::count() ?: $this->perPage;
        
        $subject = Subject::withCount([
            'exams as questions_count',
            'topicAndSources as topic_count',
        ])->get()->map(function($item) {
            // Count favorites for this subject's questions
            $item->favorite_count = Favorite::whereIn('question_id', 
                ExamQuestion::where('subject_id', $item->id)->pluck('id')
            )->where('user_id', auth()->id())
            ->where('category', 'revision')
            ->count();
            
            // Count read questions for this subject
            $item->read_count = ExamQuestionRead::whereIn('exam_question_id', 
                ExamQuestion::where('subject_id', $item->id)->pluck('id')
            )->where('user_id', auth()->id())
            ->where('is_read', 1)
            ->count();
            
            return $item;
        });

        // Manually create pagination
        $currentPage = request()->get('page', 1);
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $subject->forPage($currentPage, $perPage),
            $subject->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return $this->successMessage('Data fetched successfully', $paginated);
    }

    // getRevisionTopicList
    public function getRevisionTopicList($id)
    {
        // with pagination
        $perPage = TopicSource::where('subject_id', $id)->count() ?: $this->perPage;
        
        $topic = TopicSource::withCount([
            'questions as questions_count',
        ])
            ->where('subject_id', $id)
            ->get()
            ->map(function($item) {
                // Count favorites for this topic's questions
                $item->favorite_count = Favorite::whereIn('question_id', 
                    ExamQuestion::where('topic_id', $item->id)->pluck('id')
                )->where('user_id', auth()->id())
                ->where('category', 'revision')
                ->count();
                
                // Count read questions for this topic
                $item->read_count = ExamQuestionRead::whereIn('exam_question_id', 
                    ExamQuestion::where('topic_id', $item->id)->pluck('id')
                )->where('user_id', auth()->id())
                ->where('is_read', 1)
                ->count();
                
                return $item;
            });

        // Manually create pagination
        $currentPage = request()->get('page', 1);
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $topic->forPage($currentPage, $perPage),
            $topic->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return $this->successMessage('Data fetched successfully', $paginated);
    }

    //getRevisionQuestionList
    public function getRevisionQuestionList($id)
    {
        // with pagination
        $perPage = ExamQuestion::where('topic_id', $id)->count() ?: $this->perPage;
        
        $question = ExamQuestion::where('exam_questions.topic_id', $id)
            ->with([
                'questionOptions',
                'isFavorite',
                'isRead'
            ])
            ->leftJoin('exam_question_reads', function ($join) {
                $join->on('exam_questions.id', '=', 'exam_question_reads.exam_question_id')
                    ->where('exam_question_reads.user_id', auth()->id());
            })
            ->select('exam_questions.*', 'exam_question_reads.is_read')
            ->orderByRaw('COALESCE(exam_question_reads.is_read, 0) ASC')
            ->get();

        // Manually create pagination
        $currentPage = request()->get('page', 1);
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $question->forPage($currentPage, $perPage),
            $question->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return $this->successMessage('Data fetched successfully', $paginated);
    }

    public function getRevisionQuestionListSubject($id)
    {
        // with pagination
        $perPage = ExamQuestion::where('subject_id', $id)->count() ?: $this->perPage;
        
        $question = ExamQuestion::where('exam_questions.subject_id', $id)
            ->with([
                'questionOptions',
                'isFavorite',
                'isRead'
            ])
            ->leftJoin('exam_question_reads', function ($join) {
                $join->on('exam_questions.id', '=', 'exam_question_reads.exam_question_id')
                    ->where('exam_question_reads.user_id', auth()->id());
            })
            ->select('exam_questions.*', 'exam_question_reads.is_read')
            ->orderByRaw('COALESCE(exam_question_reads.is_read, 0) ASC')
            ->get();

        // Manually create pagination
        $currentPage = request()->get('page', 1);
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $question->forPage($currentPage, $perPage),
            $question->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return $this->successMessage('Data fetched successfully', $paginated);
    }

    // revisionQuestionFavorite

    public function revisionQuestionFavorite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|exists:exam_questions,id',
            'is_favorite' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Get the question to retrieve subject_id
        $question = ExamQuestion::find($request->question_id);

        if ($request->is_favorite) {
            // Add to favorites
            $favorite = Favorite::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'question_id' => $request->question_id,
                    'category' => 'revision',
                ],
                [
                    'subcategory' => 'exam',
                    'childcategory' => null,
                    'subject_id' => $question->subject_id,
                ]
            );

            return $this->successMessage('Question added to favourite', $favorite);
        } else {
            // Remove from favorites
            Favorite::where('user_id', auth()->id())
                ->where('question_id', $request->question_id)
                ->where('category', 'revision')
                ->delete();

            return $this->successMessage('Question removed from favorite');
        }
    }

    // revisionQuestionRead
    public function revisionQuestionRead(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|exists:exam_questions,id',
            'is_read' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $read = ExamQuestionRead::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'exam_question_id' => $request->question_id
            ],
            [
                'is_read' => $request->is_read
            ]
        );

        return $this->successMessage('Data fetched successfully', $read);
    }

}
