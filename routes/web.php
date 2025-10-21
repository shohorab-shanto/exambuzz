<?php

use App\Http\Controllers\Backend\AdminAuthenticationController;
use App\Http\Controllers\Backend\AdminManagementController;
use App\Http\Controllers\Backend\CompanyInfoController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\ExamController;
use App\Http\Controllers\Backend\MaterialController;
use App\Http\Controllers\Backend\MaterialFolderController;
use App\Http\Controllers\Backend\NoticeBoardController;
use App\Http\Controllers\Backend\PackageController;
use App\Http\Controllers\Backend\PageController;
use App\Http\Controllers\Backend\RevisionSubjectController;
use App\Http\Controllers\Backend\RevisionTopicSourceController;
use App\Http\Controllers\Backend\SubjectController;
use App\Http\Controllers\Backend\TeacherExamAssignController;
use App\Http\Controllers\Backend\TeacherManagementController;
use App\Http\Controllers\Backend\TopicSourceController;
use App\Http\Controllers\BkashController;
use App\Models\Page;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
 */
Route::get('/cc', function () {
    Artisan::call('written:written-notification');

    return 'ok';
});
Route::get('/deletion-policy', function () {

    return view('delineation_policy');
});

Route::controller(AdminAuthenticationController::class)->middleware('guest:admin')->group(function () {

    Route::get('/', 'login')->name('login');
    Route::post('/store-login', 'storeLogin')->name('storeLogin');
    Route::get('/forgot-password', 'forgotPassword')->name('forgotPassword');
    Route::post('/store-forgot-password', 'storeForgotPassword')->name('storeForgotPassword');
    Route::get('/reset-password/{token}', 'resetPassword')->name('resetPassword');
    Route::post('/store-reset-password', 'storeResetPassword')->name('storeResetPassword');
});

Route::middleware('auth:admin')->group(function () {

    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'dashboard')->name('dashboard');
        Route::post('/logout', 'logout')->name('logout');
        Route::get('/students', 'students')->name('students');
        Route::get('/change-student-status/{id}', 'changeStudentStatus')->name('changeStudentStatus');
        Route::get('/show-student-details/{id}', 'showStudentDetails')->name('showStudentDetails');
        Route::get('/student-package-history/{id}', 'studentPackageHistory')->name('studentPackageHistory');
        Route::delete('/student-package-history/{id}', 'studentPackageHistoryDelete')->name('studentPackageHistoryDelete');

        Route::get('/student-request', 'studentRequest')->name('studentRequest');
    });

    Route::controller(AdminManagementController::class)->prefix('/admin')->name('admin.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/edit/{admin}', 'edit')->name('edit');
        Route::put('/update/{admin}', 'update')->name('update');
        Route::delete('/delete/{admin}', 'delete')->name('delete');
    });

    Route::controller(SubjectController::class)->prefix('/subject')->name('subject.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/edit/{subject}', 'edit')->name('edit');
        Route::put('/update/{subject}', 'update')->name('update');
        Route::delete('/delete/{subject}', 'delete')->name('delete');

        // getSubjects
        Route::get('/getSubjects', 'getSubjects')->name('getSubjects'); //ajax request

    });

    Route::controller(RevisionSubjectController::class)->prefix('/revision-subject')->name('revision_subject.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/edit/{subject}', 'edit')->name('edit');
        Route::put('/update/{subject}', 'update')->name('update');
        Route::delete('/delete/{subject}', 'delete')->name('delete');
    });

    Route::controller(TopicSourceController::class)->prefix('/topic-source')->name('topic.source.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/edit/{topic_source}', 'edit')->name('edit');
        Route::put('/update/{topic_source}', 'update')->name('update');
        Route::delete('/delete/{topic_source}', 'delete')->name('delete');
    });

    Route::controller(RevisionTopicSourceController::class)->prefix('/revision-topic-source')->name('revision_topic.source.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/edit/{topic_source}', 'edit')->name('edit');
        Route::put('/update/{topic_source}', 'update')->name('update');
        Route::delete('/delete/{topic_source}', 'delete')->name('delete');


        Route::get('/mcq-question/{topic_id}', 'mcqQuestion')->name('mcqQuestion');
        Route::post('/create-or-update-mcq-question/{exam_id}', 'createOrUpdateMCQQuestion')->name('createOrUpdateMCQQuestion');
        Route::get('/delete-question/{question_id}', 'deleteQuestion')->name('deleteQuestion');
    });

    Route::controller(ExamController::class)->prefix('/exam')->name('exam.')->group(function () {
        Route::get('/written-meritlist/{id}', 'writtenMeritlist')->name('writtenMeritlist');
        Route::get('/written-meritlist-download/{id}', 'writtenMeritlistDownload')->name('writtenMeritlistDownload');
        /**
         * preliminary
         */
        Route::get('/index', 'index')->name('index');
        Route::get('/create/{exam_id?}', 'create')->name('create');
        Route::any('/store-or-update/{exam_id?}', 'storeOrUpdate')->name('storeOrUpdate');

        Route::get('/mcq-question/{exam_id}', 'mcqQuestion')->name('mcqQuestion');
        Route::post('/create-or-update-mcq-question/{exam_id}', 'createOrUpdateMCQQuestion')->name('createOrUpdateMCQQuestion');
        Route::get('/delete-question/{question_id}', 'deleteQuestion')->name('deleteQuestion');

        /**
         * written
         */
        Route::get('/written', 'written')->name('written');
        Route::get('/written-create/{written_id?}', 'writtenCreate')->name('writtenCreate');
        Route::any('/written-store-or-update/{written_id?}', 'writtenStoreOrUpdate')->name('writtenStoreOrUpdate');

        Route::get('/written-question/{written_id}', 'writtenQuestion')->name('writtenQuestion');
        Route::post('/create-or-update-written-question/{written_id}', 'createOrUpdateWrittenQuestion')->name('createOrUpdateWrittenQuestion');
        Route::get('/delete-written-question/{question_id}', 'deleteWrittenQuestion')->name('deleteWrittenQuestion');

        /**
         * syllabus
         */
        Route::get('/syllabus', 'syllabus')->name('syllabus');
        Route::post('/upload-syllabus', 'uploadSyllabus')->name('uploadSyllabus');

        Route::post('/get-topic', 'getTopic')->name('getTopic'); //ajax request
    });

    Route::controller(MaterialController::class)->prefix('/material')->name('material.')->group(function () {
        Route::get('/index', 'index')->name('index');
        Route::get('/create/{material_id?}', 'create')->name('create');
        Route::any('/store-or-update/{material_id?}', 'storeOrUpdate')->name('storeOrUpdate');
        Route::delete('/delete/{id}', 'delete')->name('delete');
        Route::post('/get-topic', 'getTopic')->name('getTopic'); //ajax request
    });

    Route::prefix('/material')->name('material.')->group(function () {
        Route::resource('/folder', MaterialFolderController::class);
        // MaterialFolderController
    });

    Route::resource('/notice-board', NoticeBoardController::class);

    /**
     * teacher section
     */
    Route::prefix('/teacher')->name('teacher.')->group(function () {
        Route::controller(TeacherManagementController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create-or-edit/{id?}', 'createOrEdit')->name('createOrEdit');
            Route::post('/store-or-update/{id?}', 'storeOrUpdate')->name('storeOrUpdate');
            Route::get('/show/{id}', 'show')->name('show');
            Route::get('/reviews/{id}', 'showReviews')->name('reviews');
            Route::post('/add-admin-reply', 'addAdminReply')->name('addAdminReply');
            Route::post('/edit-conversation-message', 'editConversationMessage')->name('editConversationMessage');
            Route::post('/delete-conversation-message', 'deleteConversationMessage')->name('deleteConversationMessage');

            //wallet
            Route::get('/withdrawal-request', 'withdrawalRequest')->name('withdrawalRequest');
            Route::post('/update-withdrawal-request/{id}', 'updateWithdrawalRequest')->name('updateWithdrawalRequest');
        });

        Route::controller(TeacherExamAssignController::class)->prefix('/written')->name('written.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/assign-paper/{written_id}/{category}', 'assignPaper')->name('assignPaper');
            Route::post('/store-assign-paper', 'storeAssignPaper')->name('storeAssignPaper');
            Route::get('/removed-assign-teacher/{id}', 'removedAssignTeacher')->name('removedAssignTeacher');
            Route::get('/recheck-assign-teacher/{id}', 'recheckAssignTeacher')->name('recheckAssignTeacher');
            Route::get('/written-meritlist/{id}', 'writtenMeritlist')->name('writtenMeritlist');
            Route::get('/all-student-list/{id}', 'all_student_list')->name('all_student_list');

            Route::get('/delete-all-paper/{id}/{type}', 'delete_all_paper')->name('delete_all_paper');

            Route::get('/written-answer-resubmit/{id}/{written_id}', 'resubmit_written')->name('resubmit_written');
            Route::get('/written-answer-show-paper/{id}/{written_id}', 'resubmit_written_show_paper')->name('resubmit_written_show_paper');

            Route::get('/written-answer-show-paper-image/{id}/{written_id}/{question_id}', 'resubmit_written_show_paper_image')->name('resubmit_written_show_paper_image');
            Route::get('/written-meritlist-download/{id}', 'writtenMeritlistDownload')->name('writtenMeritlistDownload');
        });
    });

    Route::get('/company-info', [CompanyInfoController::class, 'showCompanyInfo'])->name('showCompanyInfo');
    Route::post('/company-info', [CompanyInfoController::class, 'storeCompanyInfo'])->name('storeCompanyInfo');

    Route::controller(PageController::class)->prefix('/page')->name('page.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/edit/{page}', 'edit')->name('edit');
        Route::put('/update/{page}', 'update')->name('update');
        Route::delete('/delete/{page}', 'delete')->name('delete');
    });

    Route::controller(PackageController::class)->prefix('/packages')->name('packages.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create-or-edit/{id?}', 'createOrEdit')->name('createOrEdit');
        Route::post('/store-or-update/{id?}', 'storeOrUpdate')->name('storeOrUpdate');
        Route::delete('/delete/{id}', 'destroy')->name('delete');
        Route::get('/active-package', 'active_package')->name('active_package');
        Route::post('/save-active-package', 'save_active_package')->name('save_active_package');
    });
});

Route::get('/privacy-policy', function () {
    $data = Page::where('slug', 'privacy-policy')->first();

    return Blade::render('
        <h1>{{$data->name}}</h1> <br>
        <p>{!! $data->details !!}</p>
    ', ['data' => $data]);
});


// Checkout (URL) User Part
Route::get('/bkash-pay', [BkashController::class, 'payment'])->name('url-pay');
Route::post('/bkash-create', [BkashController::class, 'createPayment'])->name('url-create');
Route::get('/bkash-callback', [BkashController::class, 'callback'])->name('url-callback');

// Checkout (URL) Admin Part
Route::get('/bkash-refund', [BkashController::class, 'getRefund'])->name('url-get-refund');
Route::post('/bkash-refund', [BkashController::class, 'refundPayment'])->name('url-post-refund');
Route::get('/bkash-search', [BkashController::class, 'getSearchTransaction'])->name('url-get-search');
Route::post('/bkash-search', [BkashController::class, 'searchTransaction'])->name('url-post-search');
Route::get('/bkash-query/{paymentID}', [BkashController::class, 'queryPaymentAPI'])->name('url-get-query');
