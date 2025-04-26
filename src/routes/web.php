<?php
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AdminController;

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
// ログイン画面表示
Route::get('/admin/login', function () {
    return view('auth.admin_login');
});

// ログイン処理
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/admin/login', [AuthenticatedSessionController::class, 'store']);
// ログアウト処理
Route::post('/logout', [AuthenticatedSessionController::class, 'logout']);
Route::post('/admin/logout', [AuthenticatedSessionController::class, 'logout']);




// ログイン後の画面表示
Route::get('/attendance', [StaffController::class, 'attendanceView']);
Route::get('/attendance/list', [StaffController::class, 'attendanceListView']);
Route::get('/stamp_correction_request/list', [StaffController::class, 'requestListView']);
Route::get('/attendance/id', [StaffController::class, 'attendanceDetail']);

Route::get('/admin/attendance/list',[AdminController::class,'attendanceList']);
Route::get('/admin/staff/list', [AdminController::class, 'staffList']);
Route::get('/admin/attendance/staff/id', [AdminController::class, 'staffAttendanceList']);
Route::get('admin/attendance/id', [AdminController::class, 'attendanceDetail']);
Route::get('/admin/stamp_correction_request/list', [AdminController::class, 'requestList']);
Route::get('/admin/stamp_correction_request/approve/', [AdminController::class, 'approval']);