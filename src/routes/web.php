<?php

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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/admin/login', [AdminController::class, 'adminLogin']);
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