<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// ログイン画面表示(管理者)
Route::get('/admin/login', function () {
    return view('auth.admin_login');
});
// ログイン画面表示(スタッフ)
Route::get('/login', function () {
    return view('auth.staff_login');
})->name('login');
//会員登録
Route::post('/register', [RegisterController::class,'store']);
// ログイン処理
Route::post('/login', [LoginController::class, 'store']);
Route::post('/admin/login', [LoginController::class, 'store']);
// ログアウト処理
Route::post('/logout', [LoginController::class, 'destroy']);
Route::post('/admin/logout', [LoginController::class, 'destroy']);

// ログイン後の画面表示(ユーザーのみ)
Route::group(['middleware' => ['auth', 'can:user-higher','verified']],function(){
    Route::get('/attendance', [StaffController::class, 'attendanceView']);
    Route::get('/attendance/list/{year?}/{month?}', [StaffController::class, 'attendanceListView']);
    Route::get('/stamp_correction_request/list', [StaffController::class, 'requestListView']);
    Route::get('/stamp_correction_request/list/approval',[StaffController::class,'requestListView']);
    Route::get('/attendance/{attendaceId?}/{applicationId?}', [StaffController::class, 'attendanceDetail']);
    //出退勤・休憩機能
    Route::post('/attendance/clockin', [StaffController::class, 'AddClockIn']);
    Route::post('/attendance/clockout', [StaffController::class, 'AddClockOut']);
    Route::post('/attendance/restin', [StaffController::class, 'AddRestIn']);
    Route::post('/attendance/restout', [StaffController::class, 'AddRestOut']);

    //勤怠修正機能(スタッフ)
    Route::post('/attendance/application', [StaffController::class, 'application']);

});
// ログイン後の画面表示(管理者のみ)
Route::group(['middleware' => ['auth', 'can:admin-higher']], function () {
    Route::get('/admin/attendance/list/{year?}/{month?}/{day?}', [AdminController::class, 'attendanceList']);
    Route::get('/admin/staff/list', [AdminController::class, 'staffList']);
    Route::get('/admin/attendance/staff/{id?}/{year?}/{month?}', [AdminController::class, 'staffAttendanceList']);
    Route::get('/admin/attendance/{id?}', [AdminController::class, 'attendanceDetail']);
    Route::get('/admin/stamp_correction_request/list', [AdminController::class, 'requestList']);
    Route::get('/admin/stamp_correction_request/list/approval', [AdminController::class, 'requestList']);
    Route::get('/admin/stamp_correction_request/approve/{id?}', [AdminController::class, 'viewApproval']);
    //勤怠修正機能(管理者)
    Route::post('/admin/attendance/application', [AdminController::class, 'application']);

    //承認機能
    Route::post('/admin/stamp_correction_request/approve', [AdminController::class, 'approval']);
    //csvダウンロード
    Route::post('/export', [AdminController::class, 'export']);
});



//メール認証画面の表示
Route::get('/email/verify', function () {
    return view('auth.email_verify');
})->middleware('auth')->name('verification.notice');

//
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/attendance');
})->middleware(['auth', 'signed'])->name('verification.verify');

//メール再送
Route::post('/email/resend', function (Request $request) {
    Auth::user()->sendEmailVerificationNotification();
    return back()->with('message', '認証メールを再送しました。');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Route::get('/email/verify', [RegisterController::class, 'emailVerify']);