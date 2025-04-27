<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminController extends Controller
{
    //当日の勤怠一覧画面表示
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
        $users = User::where('role',0)->get();
        return view('admin.admin_staff_list',compact('users'));
    }
    //スタッフ別勤怠月次一覧
    public function staffAttendanceList()
    {
        return view('admin.admin_staff_attendance_list');
    }
    //申請一覧表示
    public function requestList()
    {
        return view('admin.admin_request_list');
    }
    //修正承認画面表示
    public function approval()
    {
        return view('admin.admin_approval');
    }

}
