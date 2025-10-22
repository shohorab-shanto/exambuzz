<?php

use App\Http\Controllers\Api\AnswerController;
use App\Http\Controllers\Api\ExamManageController;
use App\Http\Controllers\Api\FirebaseAuthController;
use App\Http\Controllers\Api\RevisionController;
use App\Http\Controllers\Api\SocialLoginController;
use App\Http\Controllers\Api\SubscribtionController;
use App\Http\Controllers\Api\TeacherPanelController;
use App\Http\Controllers\Api\UserAuthController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Backend\NoticeBoardController;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\GoogleController;
use App\Models\CompanyInfo;
use App\Models\Exam;
use App\Models\Material;
use App\Models\Notification;
use App\Models\Page;
use App\Models\Subject;
use App\Models\TopicSource;
use App\Models\User;
use App\Models\Written;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
 */

// Firebase Authentication (API Only - for Mobile Apps)
Route::post('/firebase/google-login', [FirebaseAuthController::class, 'googleLogin']);
Route::post('/firebase/facebook-login', [FirebaseAuthController::class, 'facebookLogin']);

// Legacy OAuth routes (Web-based - Optional)
Route::controller(GoogleController::class)->group(function () {
    Route::get('auth/google', 'redirectToGoogle')->name('auth.google');
    Route::get('auth/google/callback', 'handleGoogleCallback');
});

// Legacy Social Login (Direct API - Old method)
Route::post('/login/facebook', [SocialLoginController::class, 'facebook_login']);
Route::post('/login/google', [SocialLoginController::class, 'google_login']);

Route::get('/get-notice-board', [NoticeBoardController::class, 'all_notice_board']);

Route::controller(FacebookController::class)->group(function () {
    Route::get('auth/facebook', 'redirectToFacebook')->name('auth.facebook');
    Route::get('auth/facebook/callback', 'handleFacebookCallback');
});

Route::middleware('auth:sanctum')->get('/logout', function (Request $request) {
    $user = $request->user();
    $user->tokens()->delete();
    $user->fcm_token = NULL;
    $user->save();
    Auth::guard('web')->logout();

    return ['status' => true, 'message' => 'Logout Successful!'];
});

Route::middleware('auth:sanctum')->post('/contact-us', function (Request $request) {
    $data = CompanyInfo::find(1);

    return response()->json([
        'status' => true,
        'data' => $data,
    ]);

});

Route::middleware('auth:sanctum')->post('/account-deletation/{user_id}', function (Request $request, $user_id) {
    $data = User::find($user_id);

    $data->status = 0;
    $data->save();

    return response()->json([
        'status' => true,
        'data' => $data,
    ]);

});

Route::post('/privacy-policy', function (Request $request) {
    $data = [];
    $data['privacy'] = Page::where('slug', 'privacy-policy')->first();
    $data['terms'] = Page::where('slug', 'terms-and-conditions')->first();

    return response()->json([
        'status' => true,
        'data' => $data,
    ]);

});
Route::middleware('auth:sanctum')->post('/store-fcm-token', function (Request $request) {

    $data = User::find(Auth::id());
    $data->fcm_token = $request->fcm_token;
    $data->save();

    return response()->json([
        'status' => true,
        'data' => $data,
    ]);

});
Route::middleware('auth:sanctum')->post('/notification', function (Request $request) {

    $data = Notification::where('user_id', Auth::id())->with('user', 'written')->orderBy('id', 'desc')->paginate();

    return response()->json([
        'status' => true,
        'data' => $data,
    ]);

});
Route::middleware('auth:sanctum')->post('/make-notification-seen', function (Request $request) {

    $data = Notification::where('user_id', Auth::id())->with('user', 'written')->orderBy('id', 'desc')->paginate();

    return response()->json([
        'status' => true,
        'data' => $data,
    ]);

});


Route::middleware('auth:sanctum')->post('/v2/get-material', function (Request $request) {

// Assuming $request is an instance of Illuminate\Http\Request

    $materialFolders = \App\Models\MaterialFolder::where('type', $request->category)->with('materials');

    if ($request->has('subject_id')) {
        $materialFolders->whereHas('materials', function ($query) use ($request) {
            $query->where('subject_id', 'LIKE', '%' . $request->subject_id . '%');
        });
    }

    if ($request->has('search')) {
        $materialFolders->whereHas('materials', function ($query) use ($request) {
            $query->where('name', 'LIKE', $request->search . '%');
        });
    }

    $data = $materialFolders->latest()->paginate();

    $data->load(['materials.subjects', 'materials.sources']);

    // This line is unnecessary since you're paginating the results and assigning the paginated results to $data.
    // $data['material'] = $material;

    foreach ($data as $folder) {
        foreach ($folder->materials as $item) {
            // Assuming $item->subject_id and $item->topic_id are comma-separated strings like '1,2,3'
            $item->subjects = Subject::whereIn('id', explode(',', $item->subject_id))->get();
            $item->sources = TopicSource::whereIn('id', explode(',', $item->topic_id))->get();
        }
    }


    return response()->json([
        'status' => true,
        'data' => $data,
    ]);

});
Route::middleware('auth:sanctum')->post('/get-material', function (Request $request) {
    $data = Material::where('category', $request->category);

    if ($request->subject_id) {
        $data = $data->where('subject_id', 'LIKE', '%' . $request->subject_id . '%');
    }

    if ($request->search) {

        $data = $data->where('name', 'LIKE', $request->search . '%');

    }

    $data = $data->latest()->paginate();

    foreach ($data as $item) {
        $item['subjects'] = Subject::whereIn('id', explode(',', $item->subject_id))->get();
        $item['sources'] = TopicSource::whereIn('id', explode(',', $item->topic_id))->get();
    }

    return response()->json([
        'status' => true,
        'data' => $data,
    ]);

});

Route::middleware('auth:sanctum')->get('/get-present-live-exam', function (Request $request) {

    $data = [];

    $exam = Exam::where('status', 1)->where('published_at', '<=', Carbon::now('Asia/Dhaka')->toDateTimeString())
        ->where('expired_at', '>=', Carbon::now('Asia/Dhaka')->toDateTimeString())
        ->select(['id', 'category', 'subcategory', 'childcategory'])
        ->get();

    $written = Written::where('status', 1)->where('published_at', '<=', Carbon::now('Asia/Dhaka')->toDateTimeString())
        ->where('expired_at', '>=', Carbon::now('Asia/Dhaka')->toDateTimeString())
        ->select(['id', 'category', 'subcategory', 'childcategory'])
        ->get();

    $data['exam'] = $exam;
    $data['written'] = $written;

    return response()->json([
        'status' => true,
        'data' => $data,
    ]);

});

Route::controller(UserAuthController::class)->prefix('/auth')->group(function () {
    Route::post('/register', 'register');
    Route::post('/verify-otp', 'verifyOtp');
    Route::post('/login', 'login');
    Route::post('/store-forgot-password', 'storeForgotPassword');
    Route::post('/reset-password', 'resetPassword');
    Route::post('/resend-otp', 'resendOTP');
});

Route::controller(SubscribtionController::class)->group(function () {
    Route::post('/packages', 'packages');
    Route::post('/upcoming-packages', 'upcomingPackages');
    Route::post('/purchase-package', 'purchasePackage');
    Route::post('/package-history', 'packageHistory');
    Route::post('/v2/package-history', 'packageHistoryv2')->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->controller(UserProfileController::class)->prefix('/profile')->group(function () {
    Route::get('/user', 'user');
    Route::post('/update', 'update');
    Route::post('/asking-query', 'askingQuery');
});


Route::middleware('auth:sanctum')->controller(ExamManageController::class)->prefix('/exam')->group(function () {
    Route::post('/check-live-exam', 'checkLiveExam');
    Route::post('/routine', 'routine');
    Route::post('/all-routine', 'allRoutine');
    Route::post('/archive', 'archive');
    Route::post('/syllabus', 'syllabus');
    Route::post('/archive-exam-question-details', 'archiveExamQuestionDetails');
    Route::post('/toggle-favorite', 'toggleFavorite');
    Route::post('/favorite-list', 'favoriteList');
    Route::post('/result-list', 'resultList');
    Route::post('/subject-list', 'subjectList');
    Route::post('/v2/merit-list', 'meritListv2');
    Route::post('/merit-list', 'meritList');
});

Route::middleware('auth:sanctum')->controller(AnswerController::class)->prefix('/answer')->group(function () {
    Route::post('/store-preliminary-answer', 'storePreliminaryAnswer');
    Route::post('/show-preliminary-answer', 'showPreliminaryAnswer');
    Route::post('/preliminary-answer-script', 'preliminaryAnswerScript');
    Route::post('/v2/preliminary-answer-merit-list', 'preliminaryAnswerMeritListv2');
    Route::post('/preliminary-answer-merit-list', 'preliminaryAnswerMeritList');

    Route::post('/store-written-answer', 'storeWrittenAnswer');
    Route::post('/v2/store-written-answer', 'storeWrittenAnswerv2');

    // Review system routes (Conversation Thread Style)
    Route::post('/submit-review', 'submitReview'); // Student submits rating + comment
    Route::post('/add-conversation', 'addConversationMessage'); // Add message to conversation (student/teacher/admin)
    Route::post('/get-conversation', 'getReviewConversation'); // Get full conversation thread
    Route::post('/reply-review', 'replyToReview'); // Legacy - redirects to add-conversation
});

Route::middleware('auth:sanctum')->controller(TeacherPanelController::class)->prefix('/teacher')->group(function () {
    Route::post('/exam-and-paper', 'examAndPaper');
    Route::post('/store-exam-paper-assessment', 'storeExamPaperAssessment');

    //wallet
    Route::post('/wallet', 'wallet');
    Route::post('/withdrawal-request', 'withdrawalRequest');

    Route::post('/dashboard', 'dashboard');
});

Route::middleware('auth:sanctum')->group(function () {
    // get Revision Subject list
    Route::get('/revision-subject-list', [RevisionController::class, 'getRevisionSubjectList']);
    Route::get('/revision-topic-list/{id}', [RevisionController::class, 'getRevisionTopicList']);
    Route::get('/revision-question-list/{id}', [RevisionController::class, 'getRevisionQuestionList']);
    Route::get('/revision-question-subject-list/{id}', [RevisionController::class, 'getRevisionQuestionListSubject']);
    Route::post('/revision-question-favorite', [RevisionController::class, 'revisionQuestionFavorite']);
    Route::post('/revision-question-read', [RevisionController::class, 'revisionQuestionRead']);
});


Route::get('/category', function () {
    return [
        'BCS' => [
            'Preliminary',
            'Written',
        ],
        'Bank' => [
            'Preliminary',
            'Written',
        ],
        'Others' => [
            'Preliminary' => [
                'Primary',
                '11 to 20 Grade',
                'Non-Cadre',
                'Job Solution',
            ],
            'Written' => [
                'Job Solution',
            ],
        ],
        'Free' => [
            'Preliminary' => [
                'Weekly',
                'Daily',
            ],
            'Written' => [
                'Weekly',
            ],
        ],
        'Recent',
        'Record Class',
    ];
});

// add apiv2.php routes here
require_once __DIR__ . '/apiv2.php';
