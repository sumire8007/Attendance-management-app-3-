<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    //管理者ログイン画面表示
    public function adminLogin()
    {
        return view('auth.admin_login');
    }

    //勤怠一覧画面表示
    public function attendanceList()
    {
        return view('admin.admin_attendance_list');
    }
    //勤怠詳細
    public function attendanceDetail()
    {
        return view('admin.admin_attendance_detail');
    }
    //スタッフ一覧表示
    public function staffList()
    {
        return view('admin.staff_list');
    }
    //申請一覧表示
    public function requestList()
    {
        return view('admin.request_list');
    }
    //修正承認画面表示
    public function approval()
    {
        return view('admin.approval');
    }
    //スタッフ別勤怠一覧
    public function staffAttendanceList()
    {
        return view('admin.staff_attendance_list');
    }
    
}
