<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    //勤怠の表示
    public function attendanceView(){
        return view('staff.attendance');
    }
    //勤怠リストの表示
    public function attendanceListView(){
        return view('staff.attendance_list');
    }
    //申請一覧の表示
    public function requestListView(){
        return view('staff.request_list');
    }
    //勤怠詳細(申請入力)
    public function attendanceDetail(){
        return view('staff.attendance_detail');
    }

}
