<?php

use App\Http\Controllers\Api\AnswerController;
use App\Http\Controllers\Api\ExamManageController;
use App\Http\Controllers\Api\FirebaseAuthController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\RevisionController;
use App\Http\Controllers\Api\SocialLoginController;
use App\Http\Controllers\Api\BkashPaymentController;
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
// Notification Routes - Using NotificationController
Route::middleware('auth:sanctum')->group(function () {
    // FCM Token Management
    Route::post('/store-fcm-token', [NotificationController::class, 'storeFcmToken']);
    Route::post('/remove-fcm-token', [NotificationController::class, 'removeFcmToken']);
    
    // Notification Management
    Route::post('/notification', [NotificationController::class, 'index']); // Get all notifications (backward compatible)
    Route::get('/notifications', [NotificationController::class, 'index']); // RESTful endpoint
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount']);
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    
    // Backward compatibility for old endpoint
    Route::post('/make-notification-seen', [NotificationController::class, 'markAllAsRead']);
});


Route::middleware('auth:sanctum')->post('/v2/get-material', function (Request $request) {

    $baseQuery = \App\Models\MaterialFolder::where('type', $request->category);

    if ($request->has('subject_id')) {
        $baseQuery->whereHas('materials', function ($query) use ($request) {
            $query->where('subject_id', 'LIKE', '%' . $request->subject_id . '%');
        });
    }

    if ($request->has('search')) {
        $baseQuery->whereHas('materials', function ($query) use ($request) {
            $query->where('name', 'LIKE', $request->search . '%');
        });
    }

    // Get ALL folders of this type (not just root folders)
    $allFolders = $baseQuery->with('materials')->latest()->get();

    // Load and process materials for ALL folders
    foreach ($allFolders as $folder) {
        if ($folder->materials && $folder->materials->count() > 0) {
            $folder->load(['materials.subjects', 'materials.sources']);
            
            foreach ($folder->materials as $item) {
                if ($item->subject_id) {
                    $item->subjects = Subject::whereIn('id', explode(',', $item->subject_id))->get();
                }
                if ($item->topic_id) {
                    $item->sources = TopicSource::whereIn('id', explode(',', $item->topic_id))->get();
                }
            }
        }
    }

    // Build hierarchical structure by creating a map and connecting parent-child relationships
    $folderMap = $allFolders->keyBy('id');
    $rootFolders = [];
    
    foreach ($allFolders as $folder) {
        if ($folder->parent_id && isset($folderMap[$folder->parent_id])) {
            // This is a child folder - add it to parent's children collection
            $parent = $folderMap[$folder->parent_id];
            if (!isset($parent->children)) {
                $parent->children = collect([]);
            }
            $parent->children->push($folder);
        } else {
            // This is a root folder
            $rootFolders[] = $folder;
        }
    }

    $hierarchicalData = collect($rootFolders);

    return response()->json([
        'status' => true,
        'data' => $hierarchicalData,
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

    // Live Preliminary exams with full details
    $exam = Exam::where('status', 1)
        ->where('published_at', '<=', Carbon::now('Asia/Dhaka')->toDateTimeString())
        ->where('expired_at', '>=', Carbon::now('Asia/Dhaka')->toDateTimeString())
        ->with([
            'questions.questionOptions',
            'questions.subject',
            'questions.topic',
            'userAnswer' => function ($q) {
                return $q->where('user_id', Auth::id());
            },
        ])
        ->get();

    foreach ($exam as $item) {
        $item['subjects'] = Subject::whereIn('id', explode(',', $item->subject_id))->get();
        $item['sources'] = TopicSource::whereIn('id', explode(',', $item->topic_id))->get();
    }

    // Live Written exams with full details
    $written = Written::where('status', 1)
        ->where('published_at', '<=', Carbon::now('Asia/Dhaka')->toDateTimeString())
        ->where('expired_at', '>=', Carbon::now('Asia/Dhaka')->toDateTimeString())
        ->with([
            'writtenQuestion',
            'userAnswer' => function ($q) {
                return $q->where('user_id', Auth::id());
            },
        ])
        ->get();

    foreach ($written as $item) {
        $item['subjects'] = Subject::whereIn('id', explode(',', $item->subject_id))->get();
        $item['sources'] = TopicSource::whereIn('id', explode(',', $item->topic_id))->get();
    }

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

// bKash Payment Gateway API Routes
Route::prefix('bkash')->controller(BkashPaymentController::class)->group(function () {
    Route::post('/create-payment', 'createPayment');
    Route::get('/callback', 'callback');
    Route::post('/callback', 'callback');
    Route::post('/check-payment-status', 'checkPaymentStatus');
    Route::post('/search-transaction', 'searchTransaction');
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

    // Teacher Reviews & Conversations
    Route::get('/reviews', 'getTeacherReviews');
    Route::get('/review/{review_id}', 'getReviewDetail');
    Route::post('/review/reply', 'replyToReview');
    Route::put('/review/reply/{review_id}', 'updateReviewReply');
    Route::delete('/review/reply/{review_id}', 'deleteReviewReply');
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

// Class Routine API Routes
Route::controller(\App\Http\Controllers\Api\ClassRoutineController::class)->prefix('class-routines')->group(function () {
    Route::get('/', 'index'); // Get all routines
    Route::get('/{type}', 'getByType'); // Get by type (preliminary/written)
});

// add apiv2.php routes here
require_once __DIR__ . '/apiv2.php';
