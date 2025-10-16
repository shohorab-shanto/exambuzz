<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RevisionFavorite;
use App\Models\RevisionRead;
use App\Models\RevisionSubject;
use App\Models\RevisionTopicQuestion;
use App\Models\RevisionTopicSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RevisionController extends Controller
{
    protected $perPage = 15;

    // getRevisionSubjectList
    public function getRevisionSubjectList()
    {
        // with pagination
        $subject = RevisionSubject::withCount([
            'questions',
            'questions as read_count' => function ($query) {
                $query->whereHas('isRead', function ($query) {
                    $query->where('is_read', 1)
                        ->where('user_id', auth()->id());
                });
            },
            'topicAndSources as topic_count',
            'questions as favorite_count' => function ($query) {
                $query->whereHas('isFavorite', function ($query) {
                    $query->where('is_favorite', 1)
                        ->where('user_id', auth()->id());
                });
            },

        ])->paginate($this->perPage);


        return $this->successMessage('Data fetched successfully', $subject);
    }

    // getRevisionTopicList
    public function getRevisionTopicList($id)
    {
        // with pagination
        $topic = RevisionTopicSource::withCount([
            'questions',
            'questions as read_count' => function ($query) {
                $query->whereHas('isRead', function ($query) {
                    $query->where('is_read', 1)
                        ->where('user_id', auth()->id());
                });
            },
            'questions as favorite_count' => function ($query) {
                $query->whereHas('isFavorite', function ($query) {
                    $query->where('is_favorite', 1)
                        ->where('user_id', auth()->id());
                });
            },
        ])
            ->where('revision_subjects_id', $id)
            ->paginate($this->perPage);

        return $this->successMessage('Data fetched successfully', $topic);
    }

    //getRevisionQuestionList
    public function getRevisionQuestionList($id)
    {
        // with pagination
        $question = RevisionTopicQuestion::where('revision_topic_source_id', $id)
            ->with([
                'questionOptions',
                'isRead' => function ($query) {
                    $query->where('user_id', auth()->id());
                },
                'isFavorite' => function ($query) {
                    $query->where('user_id', auth()->id());
                }
            ])
            ->leftJoin('revision_reads', function ($join) {
                $join->on('revision_topic_questions.id', '=', 'revision_reads.revision_topic_question_id')
                    ->where('revision_reads.user_id', auth()->id());
            })
            ->select('revision_topic_questions.*', 'revision_reads.is_read')
            ->orderBy('revision_reads.is_read', 'asc')
            ->paginate($this->perPage);

        return $this->successMessage('Data fetched successfully', $question);
    }

    public function getRevisionQuestionListSubject($id)
    {
        // with pagination
        $question = RevisionTopicQuestion::where('revision_subjects_id', $id)
            ->with([
                'questionOptions',
                'isRead' => function ($query) {
                    $query->where('user_id', auth()->id());
                },
                'isFavorite' => function ($query) {
                    $query->where('user_id', auth()->id());
                }
            ])
            ->leftJoin('revision_reads', function ($join) {
                $join->on('revision_topic_questions.id', '=', 'revision_reads.revision_topic_question_id')
                    ->where('revision_reads.user_id', auth()->id());
            })
            ->select('revision_topic_questions.*', 'revision_reads.is_read')
            ->orderBy('revision_reads.is_read', 'asc')
            ->paginate($this->perPage);

        return $this->successMessage('Data fetched successfully', $question);
    }

    // revisionQuestionFavorite

    public function revisionQuestionFavorite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|exists:revision_topic_questions,id',
            'is_favorite' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }


        $favorite = RevisionFavorite::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'revision_topic_question_id' => $request->question_id
            ],
            [
                'is_favorite' => $request->is_favorite
            ]
        );

        return $this->successMessage('Data fetched successfully', $favorite);
    }

    // revisionQuestionRead
    public function revisionQuestionRead(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|exists:revision_topic_questions,id',
            'is_read' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $favorite = RevisionRead::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'revision_topic_question_id' => $request->question_id
            ],
            [
                'is_read' => $request->is_read
            ]
        );


        return $this->successMessage('Data fetched successfully', $favorite);
    }

}
