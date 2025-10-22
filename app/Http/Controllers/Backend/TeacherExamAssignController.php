<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Notification;
use App\Models\Subject;
use App\Models\TopicSource;
use App\Models\User;
use App\Models\Written;
use App\Models\WrittenAnswer;
use App\Models\WrittenAnswerQuestion;
use App\Models\WrittenAnswerQuestionScript;
use App\Services\FCMService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TeacherExamAssignController extends Controller
{
    public function delete_all_paper($id, $type)
    {
        $written_exam = WrittenAnswer::where('written_id', $id)->get();

        $message = 'All data delete successfully';
        if (count($written_exam) > 0){
            foreach ($written_exam as $exam){
                if (count($exam->writtenAnswerQuestion) > 0) {
                    foreach ($exam->writtenAnswerQuestion as $question) {
                        if (count($question->writtenAnswerQuestionScript) > 0) {
                            foreach ($question->writtenAnswerQuestionScript as $answer) {
                                if ($type == 'student') {
                                    if (file_exists($answer->student_script)) {
                                        unlink($answer->student_script);
                                        WrittenAnswerQuestionScript::where('id', $answer->id)->update([
                                            'student_script' => 'remove.gif',
                                            'deleted_at' => Carbon::now()
                                        ]);
                                        // File exists and has been deleted
                                        $message = 'All student paper delete successfully';
                                    }
                                } else if ($type == 'teacher') {
                                    if (file_exists($answer->teacher_script)) {
                                        unlink($answer->teacher_script);
                                        WrittenAnswerQuestionScript::where('id', $answer->id)->update([
                                            'teacher_script' => 'remove.gif',
                                            'deleted_at' => Carbon::now()
                                        ]);
                                        $message = 'All teacher paper delete successfully';
                                        // File exists and has been deleted
                                    }
                                }
                            }
                        }
                    }
                }
            }

        }


        return back()->withToastInfo($message);
    }

    public function resubmit_written_show_paper_image($id, $written_id, $question_id)
    {
        $data['exam'] = WrittenAnswer::with('writtenAnswerQuestion')
            ->where('user_id', $id)
            ->where('written_id', $written_id)
            ->first();

        $data['question'] = WrittenAnswerQuestion::with('writtenAnswerQuestionScript')
            ->where('id', $question_id)
            ->first();

        return view('backend.teacher.exam.show_image', $data);

    }

    public function resubmit_written_show_paper($id, $written_id)
    {
        $data['exam'] = WrittenAnswer::with(['writtenAnswerQuestion.writtenAnswerQuestion' => function($query) {
            $query->orderBy('name', 'desc');
        }])
            ->where('user_id', $id)
            ->where('written_id', $written_id)
            ->first();


        return view('backend.teacher.exam.show_paper', $data);

    }

    public function resubmit_written($id, $written_id)
    {
        $exam_find = WrittenAnswer::where('user_id', $id)->where('written_id', $written_id)->first();

        if (!isset($exam_find)) {
            return back()->withToastInfo('Exam not found.');
        }

        if ($exam_find->teacher_id) {
            return back()->withToastInfo('Please remove assigned teacher.');
        }

        $exam_find->update(['deleted_at' => Carbon::now()]);

        $exam_find->writtenAnswerQuestion()->delete();

        return redirect()->route('teacher.written.all_student_list', $written_id)->withToastSuccess('Student re-submit option enable.');
    }

    public function all_student_list($written_id)
    {
        $data = [];

        $exam = WrittenAnswer::where('written_id', $written_id)->orderBy('obtained_mark', 'desc');

        if (request()->registration_id) {
            $exam = $exam->whereHas('user', function ($q) {
                $q->where('registration_id', request()->registration_id);
            });
        }

        $exam = $exam->paginate()->withQueryString();

        $data['exam'] = $exam;
        $data['written_id'] = $written_id;

        return view('backend.teacher.exam.all_examine', $data);
    }

    public function writtenMeritlist($written_id)
    {
        $data = [];

        $exam = WrittenAnswer::where('written_id', $written_id)->where('is_checked', 1)->orderBy('obtained_mark', 'desc');

        if (request()->registration_id) {
            $exam = $exam->whereHas('user', function ($q) {
                $q->where('registration_id', request()->registration_id);
            });
        }

        $exam = $exam->paginate()->withQueryString();

        if (count($exam) == 0) {
            return back()->withToastInfo('Still now for this exam no answer is submitted. So there is nothing to show any of student merit list.');
        }

        $data['exam'] = $exam;

        return view('backend.teacher.exam.meritlist', $data);
    }

    public function writtenMeritlistDownload($written_id)
    {
        $data = [];

        $data['find_exam'] = $find_exam = Written::where('id', $written_id)->select('id', 'pass_marks', 'expired_at')->first();

        $exam = WrittenAnswer::where('written_id', $written_id)->where('is_checked', 1)->orderBy('obtained_mark', 'desc')->with('written')->get();

        $data['exam'] = $exam;

        $pdf = Pdf::loadView('backend.teacher.exam.meritlistdownload', $data);

        return $pdf->stream();

        // return view('backend.teacher.exam.meritlistdownload', $data);
    }

    public function index(Request $request)
    {
        $data = [];
        $data['category'] = $category = $request->ref;
        $data['subcategory'] = $sub = $request->type;
        $data['childcategory'] = $child = $request->child;

        $exam = Written::where('category', $category)
            ->where('subcategory', $sub);

        if ($child) {
            $exam = $exam->where('childcategory', $child);
        }

        $exam = $exam->orderByDesc('id')
            ->withCount([
                'answer',
            ])
            ->paginate()
            ->withQueryString();

        foreach ($exam as $item) {
            $item['subjects'] = Subject::whereIn('id', explode(',', $item->subject_id))->get();
            $item['sources'] = TopicSource::whereIn('id', explode(',', $item->topic_id))->get();
        }

        $data['exam'] = $exam;

        return view('backend.teacher.exam.index', $data);
    }

    public function assignPaper($written_id, $category)
    {
        $student_id = request()->student_id;

        $data = [];
        $data['written'] = Written::find($written_id);
        $data['teacher'] = $t = User::where('permission', 'LIKE', '%' . $category . '%')->get();
        $data['paper'] = WrittenAnswer::where('written_id', $written_id)
            ->where('category', $category)
            ->orderBy('teacher_id', 'asc')
            ->with('user')
            ->whereHas('user', function ($query) use ($student_id) {
                if ($student_id) {
                    $query->where('users.registration_id', $student_id);
                }
            })
            ->paginate();

        return view('backend.teacher.exam.assign-paper', $data);
    }

    public function storeAssignPaper(Request $request)
    {

        if (!isset($request->written_answer_id)) {
            return back()->withToastInfo('No paper selected');
        }

        if ($request->teacher_id == null) {
            return back()->withToastInfo('No teacher selected');
        }

        WrittenAnswer::whereIn('id', $request->written_answer_id)
            ->update(['teacher_id' => $request->teacher_id]);

        return back()->withToastSuccess('Paper assigned successfully');

    }

    public function removedAssignTeacher($id)
    {
        $answer = WrittenAnswer::find($id);

        if ($answer->is_checked == 1) {
            return back()->withToastError('No Cheating');
        }

        $answer->teacher_id = null;
        $answer->save();

        return back()->withToastSuccess('Teacher removed from paper');
    }

    public function recheckAssignTeacher($id)
    {
        $answer = WrittenAnswer::find($id);

        $answer->is_checked = 2;
        $answer->save();

        return back()->withToastSuccess('Paper is recheck able now');
    }

    public function reassignTeacherForRecheck(Request $request)
    {
        // Validate the request
        if (!isset($request->paper_id) || empty($request->paper_id)) {
            return back()->withToastError('No paper selected');
        }

        if ($request->new_teacher_id == null || empty($request->new_teacher_id)) {
            return back()->withToastError('No teacher selected');
        }

        $answer = WrittenAnswer::find($request->paper_id);

        if (!$answer) {
            return back()->withToastError('Paper not found');
        }

        // Check if paper is already checked
        if ($answer->is_checked != 1) {
            return back()->withToastError('Only checked papers can be reassigned for recheck');
        }

        // Store the old teacher info for notification/logging if needed
        $old_teacher_id = $answer->teacher_id;

        // Reassign to new teacher and mark for recheck
        $answer->teacher_id = $request->new_teacher_id;
        $answer->is_checked = 2; // Mark as assigned for recheck
        $answer->save();

        return back()->withToastSuccess('Paper successfully reassigned to new teacher for recheck');
    }

}
