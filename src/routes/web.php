<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ManagerController;

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

Route::get('/admin/login', [ManagerController::class, 'adminLogin']);
Route::get('/attendance', [StaffController::class, 'attendanceView']);
Route::get('/attendance/list', [StaffController::class, 'attendanceListView']);
Route::get('/stamp_correction_request/list', [StaffController::class, 'requestView']);
// Route::get('/attendance/{id}')

Route::get('/admin/attendance/list',[ManagerController::class,'attendanceList']);
